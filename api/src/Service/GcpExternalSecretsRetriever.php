<?php

declare(strict_types=1);

namespace App\Service;

use Google\ApiCore\ApiException;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use RuntimeException;

use function count;

final readonly class GcpExternalSecretsRetriever
{
    private const SECRET_NAME_KEY = 'secretName';
    private const LATEST_SECRET_TAG = 'latest';

    /**
     * @param array<string, (array<string, string>|null)> $template
     *
     * @psalm-api
     */
    public function __construct(private array $template, private string $appName, private string $environmentName,)
    {
    }

    /**
     * @return array<string, string>
     *
     * @throws ApiException
     */
    public function getAllSecrets(string $projectId): array
    {
        $client = new SecretManagerServiceClient();

        $variables = [];

        foreach ($this->template as $variableName => $variableOptions) {
            $secretName = implode(
                '-',
                [$this->appName, $this->environmentName, $variableOptions[self::SECRET_NAME_KEY] ?? $variableName],
            );

            $secretVersion = ($variableOptions['$secretVersion'] ?? self::LATEST_SECRET_TAG);

            $secretFqn = $client::secretVersionName($projectId, $secretName, $secretVersion);
            $response = $client->accessSecretVersion($secretFqn);

            $payload = $response->getPayload();

            if ($payload === null) {
                throw new RuntimeException("Cannot retrieve \"$secretName\" secret. (\"$secretFqn\")");
            }

            $variables[$variableName] = $payload->getData();
        }

        if (count($variables) === 0) {
            throw new RuntimeException('Fetched 0 secrets from external vault.');
        }

        return $variables;
    }
}
