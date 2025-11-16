<?php

declare(strict_types=1);

namespace App\Command;

use App\Controller\StatusController;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function is_array;

/** @psalm-api */
#[AsCommand(name: 'status', description: 'Add a short description for your command')]
final class StatusCommand extends Command
{
    public function __construct(private readonly StatusController $statusController)
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $payload = $this->statusController->constructResponsePayload();

        $io->table(['Parameter', 'Value'], $this->formatAsTable($payload));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, string|array<string, int|string>> $data
     * @return list<array{string, int|string}>
     */
    private function formatAsTable(array $data): array
    {
        $rows = [];

        foreach ($data as $index => $value) {
            if (is_array($value)) {
                foreach ($value as $subIndex => $subValue) {
                    $rows[] = [$index . '.' . $subIndex, $subValue];
                }

                continue;
            }

            $rows[] = [$index, $value];
        }

        return $rows;
    }
}
