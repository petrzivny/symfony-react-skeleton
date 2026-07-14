<?php

declare(strict_types=1);

namespace App\Azure;

use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use SensitiveParameter;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function assert;
use function is_string;

final class AzurePostgresTokenProvider
{
    // Microsoft Entra resource ID for Azure DB for PostgreSQL Flexible Server.
    private const string RESOURCE = 'https://ossrdbms-aad.database.windows.net';
    private const string API_VERSION = '2019-08-01';
    private const int SAFETY_BUFFER_SECONDS = 300;

    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly CacheItemPoolInterface $cache,
        private readonly string $identityEndpoint,
        #[SensitiveParameter]
        private readonly string $identityHeader,
        private readonly ?string $clientId = null,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        if ($identityEndpoint === '' || $identityHeader === '') {
            throw new InvalidArgumentException(
                'IDENTITY_ENDPOINT and IDENTITY_HEADER must be non-empty. Are you running inside Azure Container Apps?',
            );
        }
    }

    /** @return non-empty-string */
    public function getToken(): string
    {
        $cacheKey = 'azpg_token.' . ($this->clientId ?? 'system');
        $item = $this->cache->getItem($cacheKey);

        $cached = $this->readCachedToken($item);

        if ($cached !== null) {
            return $cached;
        }

        $data = $this->fetchWithRetry($this->buildTokenQuery());
        $token = $this->extractAccessToken($data);
        $this->storeTokenInCache($item, $token, $data);

        return $token;
    }

    /** @return non-empty-string|null */
    private function readCachedToken(CacheItemInterface $item): ?string
    {
        if (!$item->isHit()) {
            return null;
        }

        $cached = $item->get();

        if (!is_string($cached) || $cached === '') {
            return null;
        }

        return $cached;
    }

    /** @return array<string, string> */
    private function buildTokenQuery(): array
    {
        $query = [
            'api-version' => self::API_VERSION,
            'resource' => self::RESOURCE,
        ];

        if ($this->clientId !== null) {
            $query['client_id'] = $this->clientId;
        }

        return $query;
    }

    /**
     * @param array<string, string|int> $data
     * @return non-empty-string
     */
    private function extractAccessToken(array $data): string
    {
        if (!isset($data['access_token']) || !is_string($data['access_token']) || $data['access_token'] === '') {
            throw new RuntimeException('Identity endpoint response missing access_token.');
        }

        return $data['access_token'];
    }

    /**
     * @param non-empty-string $token
     * @param array<string, string|int> $data
     */
    private function storeTokenInCache(CacheItemInterface $item, string $token, array $data): void
    {
        $expiresAt = isset($data['expires_on'])
            ? (int) $data['expires_on']
            : time() + (int) ($data['expires_in'] ?? 3_600);
        $ttl = max(60, $expiresAt - time() - self::SAFETY_BUFFER_SECONDS);

        $item->set($token)->expiresAfter($ttl);
        $this->cache->save($item);
    }

    /**
     * @param array<string, string> $query
     * @return array<string, string|int>
     */
    private function fetchWithRetry(array $query): array
    {
        $attempts = 3;
        $delayMs = 200;
        $lastError = null;

        for ($i = 1; $i <= $attempts; $i += 1) {
            try {
                return $this->requestToken($query);
            } catch (HttpExceptionInterface $e) {
                $lastError = $e;
                $this->logger->warning(
                    'Managed identity token fetch failed (attempt {n}/{max}): {msg}',
                    ['n' => $i, 'max' => $attempts, 'msg' => $e->getMessage()],
                );

                if ($i < $attempts) {
                    $this->sleepBeforeRetry($delayMs);
                    $delayMs *= 2;
                }
            }
        }

        throw new RuntimeException('Failed to obtain managed identity token.', 0, $lastError);
    }

    /**
     * @param array<string, string> $query
     * @return array<string, string|int>
     */
    private function requestToken(array $query): array
    {
        $response = $this->http->request(
            'GET',
            $this->identityEndpoint,
            [
                'query' => $query,
                'headers' => ['X-IDENTITY-HEADER' => $this->identityHeader],
                'timeout' => 5,
            ],
        )->toArray();

        $normalized = [];

        foreach ($response as $key => $value) {
            assert(is_string($key));
            assert(is_string($value) || is_int($value));
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function sleepBeforeRetry(int $delayMs): void
    {
        usleep($delayMs * 1_000);
    }
}
