<?php

declare(strict_types = 1);
namespace App\Azure;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AzurePostgresTokenProvider
{
    /** Microsoft Entra resource ID for Azure DB for PostgreSQL Flexible Server. */
    private const RESOURCE = 'https://ossrdbms-aad.database.windows.net';
    private const API_VERSION = '2019-08-01';
    private const SAFETY_BUFFER_SECONDS = 300;

    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly CacheItemPoolInterface $cache,
        private readonly string $identityEndpoint,
        #[\SensitiveParameter]
        private readonly string $identityHeader,
        private readonly ?string $clientId = null,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        if ($identityEndpoint === '' || $identityHeader === '') {
            throw new \InvalidArgumentException(
                'IDENTITY_ENDPOINT and IDENTITY_HEADER must be non-empty. '
                . 'Are you running inside Azure Container Apps?'
            );
        }
    }

    /** @return non-empty-string */
    public function getToken(): string
    {
        $cacheKey = 'azpg_token.' . ($this->clientId ?? 'system');
        $item = $this->cache->getItem($cacheKey);
        if ($item->isHit()) {
            return $item->get();
        }

        $this->logger->alert('Failed to use getToken cache.');

        $query = [
            'api-version' => self::API_VERSION,
            'resource'    => self::RESOURCE,
        ];
        if ($this->clientId !== null) {
            $query['client_id'] = $this->clientId;
        }

        $data = $this->fetchWithRetry($query);

        if (!isset($data['access_token']) || !is_string($data['access_token'])) {
            throw new \RuntimeException('Identity endpoint response missing access_token.');
        }
        $token = $data['access_token'];

        $expiresAt = isset($data['expires_on'])
            ? (int) $data['expires_on']
            : time() + (int) ($data['expires_in'] ?? 3600);
        $ttl = max(60, $expiresAt - time() - self::SAFETY_BUFFER_SECONDS);

        $item->set($token)->expiresAfter($ttl);
        $this->cache->save($item);

        return $token;
    }

    /**
     * @param array<string, string> $query
     * @return array<string, mixed>
     */
    private function fetchWithRetry(array $query): array
    {
        $attempts = 3;
        $delayMs  = 200;
        $lastError = null;

        for ($i = 1; $i <= $attempts; $i++) {

            try {
                return $this->http->request('GET', $this->identityEndpoint, [
                    'query'   => $query,
                    'headers' => ['X-IDENTITY-HEADER' => $this->identityHeader],
                    'timeout' => 5,
                ])->toArray();
            } catch (HttpExceptionInterface $e) {
                $lastError = $e;
                $this->logger->warning(
                    'Managed identity token fetch failed (attempt {n}/{max}): {msg}',
                    ['n' => $i, 'max' => $attempts, 'msg' => $e->getMessage()],
                );

                if ($i < $attempts) {
                    usleep($delayMs * 1000);
                    $delayMs *= 2;
                }
            }
        }

        throw new \RuntimeException('Failed to obtain managed identity token.', 0, $lastError);
    }
}
