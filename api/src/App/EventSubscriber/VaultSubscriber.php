<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\GcpExternalSecretsRetriever;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @psalm-api */
final readonly class VaultSubscriber implements EventSubscriberInterface
{
    public function __construct(private GcpExternalSecretsRetriever $secretsRetriever)
    {
    }

    public function onCommand(): void
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
        if ($_ENV['APP_ENV'] !== 'prod') {
            return;
        }

        $variables = $this->secretsRetriever->getAllSecrets('basic4-2542859');

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
