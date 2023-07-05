<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ApplicationMode;
use App\Service\EnvFileGenerator;
use App\Service\GcpExternalSecretsRetriever;
use Google\ApiCore\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function get_debug_type;
use function is_string;
use function sprintf;
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
    private const ARGUMENT_PROJECT = 'project';

    public function __construct(
        private readonly GcpExternalSecretsRetriever $secretsRetriever,
        private readonly EnvFileGenerator $envFileGenerator,
        private readonly LoggerInterface $logger,
        private readonly ApplicationMode $applicationMode,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_PROJECT, InputArgument::REQUIRED, 'GCP project id');
    }

    /** @throws ApiException */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $project = $input->getArgument(self::ARGUMENT_PROJECT);

        if (!is_string($project)) {
            $io->error(
                sprintf(
                    'Argument %s must be a string, instead %s provided.',
                    self::ARGUMENT_PROJECT,
                    get_debug_type($project),
                ),
            );

            return Command::FAILURE;
        }

        $variables = $this->secretsRetriever->getAllSecrets($project);

        $envFile = str_replace(self::APP_MODE_PLACEHOLDER, $this->applicationMode->value, self::DEFAULT_ENV_FILENAME);

        $this->envFileGenerator->storeEnvVariablesInFile($variables, $envFile, $this->applicationMode->value);

        $message = count($variables)
            . ' secrets successfully stored unencrypted into ' . $envFile . ' file as environmental variables.';

        $io->success($message);

        $this->logger->info($message);

        return Command::SUCCESS;
    }
}
