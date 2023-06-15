<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\EnvFileGenerator;
use App\Service\GcpExternalSecretsRetriever;
use Google\ApiCore\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Kernel;

use function assert;
use function get_debug_type;
use function is_string;
use function str_replace;

/** @psalm-api */
#[AsCommand(
    name: 'secrets:external:decrypt-to-file',
    description: 'Fetch secrets defined in template and stores them in the local .env file',
)]
final class SecretsExternalDecryptToFileCommand extends Command
{
    private const DEFAULT_ENV_FILENAME = '/var/www/html/.env.{environment}.local';

    public function __construct(
        private readonly GcpExternalSecretsRetriever $secretsRetriever,
        private readonly EnvFileGenerator $envFileGenerator,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project', InputArgument::REQUIRED, 'GCP project id')
        ;
    }

    /** @throws ApiException */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $project = $input->getArgument('project');

        if (!is_string($project)) {
            $io->error('Argument project must be a string, instead ' . get_debug_type($project) . ' provided.');

            return Command::FAILURE;
        }

        $variables = $this->secretsRetriever->getAllSecrets($project);

        $environment = $this->getEnvironment();

        $envFile = str_replace('{environment}', $environment, self::DEFAULT_ENV_FILENAME);

        $this->envFileGenerator->storeEnvVariablesInFile($variables, $envFile, $environment);

        $message = count($variables)
            . ' secrets successfully stored unencrypted into ' . $envFile . ' file as environmental variables.';

        $io->success($message);

        $this->logger->info($message);

        return Command::SUCCESS;
    }

    private function getEnvironment(): string
    {
        $application = $this->getApplication();
        assert($application instanceof Application);

        $kernel = $application->getKernel();
        assert($kernel instanceof Kernel);

        return $kernel->getEnvironment();
    }
}
