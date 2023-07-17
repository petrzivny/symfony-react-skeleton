<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\GcpExternalSecretsRetriever;
use Google\ApiCore\ApiException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @psalm-api */
final readonly class GcpSecretRetrieverSubscriber implements EventSubscriberInterface
{
    public function __construct(private GcpExternalSecretsRetriever $secretsRetriever, private ?string $projectId,)
    {
    }

    /** @throws ApiException */
    public function onCommand(): void
    {
        if ($this->projectId === null) {
            return;
        }

        $variables = $this->secretsRetriever->getAllSecrets();

        foreach ($variables as $name => $value) {
            // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
            $_ENV[$name] = $value;
        }
    }

    /**
     * @return array<string, string|array<int, int|string>>
     *
     * @psalm-suppress LessSpecificImplementedReturnType
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 99999],
        ];
    }
}
