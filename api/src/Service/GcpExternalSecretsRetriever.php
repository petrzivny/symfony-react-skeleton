<?php

declare(strict_types=1);

namespace App\Service;

use Google\ApiCore\ApiException;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use RuntimeException;

use function count;

final class GcpExternalSecretsRetriever
{
 /**
  * @param array<string, (array<string, string>|null)> $template
  * @psalm-api
  */
    public function __construct(private readonly array $template)
    {
    }

    /**
     * @return array<string, string>
     * @throws ApiException
     */
    public function getAllSecrets(string $projectId): array
    {
        $client = new SecretManagerServiceClient();

        $variables = [];

        foreach ($this->template as $variableName => $variableOptions) {
            $secretName = $variableOptions['secretName'] ?? $variableName;
            $secretVersion = $variableOptions['$secretVersion'] ?? 'latest';

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
