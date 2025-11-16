<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ApplicationMode;
use App\Service\EnvFileGenerator;
use App\Service\GcpExternalSecretsRetriever;
use Google\ApiCore\ApiException;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function str_replace;

/** @psalm-api */
#[AsCommand(
    name: 'secrets:external:decrypt-to-file',
    description: 'Fetch secrets defined in template and stores them in the local .env file',
)]
final class SecretsExternalDecryptToFileCommand extends Command
{
    private const APP_MODE_PLACEHOLDER = '{environment}';
    private const DEFAULT_ENV_FILENAME = '/var/www/html/.env.' . self::APP_MODE_PLACEHOLDER . '.local';

    public function __construct(
        private readonly GcpExternalSecretsRetriever $secretsRetriever,
        private readonly EnvFileGenerator $envFileGenerator,
        private readonly LoggerInterface $consoleLogger,
        private readonly ApplicationMode $applicationMode,
    ) {
        parent::__construct();
    }

    /** @throws ApiException */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consoleLogger->info(self::class . ' initiated.');

        $io = new SymfonyStyle($input, $output);

        $variables = $this->secretsRetriever->getAllSecrets();

        $envFile = str_replace(self::APP_MODE_PLACEHOLDER, $this->applicationMode->value, self::DEFAULT_ENV_FILENAME);

        $this->envFileGenerator->storeEnvVariablesInFile($variables, $envFile, $this->applicationMode->value);

        $message = count($variables)
            . ' secrets successfully stored unencrypted into ' . $envFile . ' file as environmental variables.';

        $io->success($message);

        $this->consoleLogger->info(self::class . ' finished with message: ' . $message);

        return Command::SUCCESS;
    }
}
