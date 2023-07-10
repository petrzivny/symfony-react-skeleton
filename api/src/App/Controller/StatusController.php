<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function array_key_exists;
use function dirname;
use function file_exists;
use function file_get_contents;
use function opcache_get_status;
use function round;
use function sprintf;
use function str_contains;

/** @psalm-api */
final class StatusController extends AbstractController
{
    public function __construct(private readonly string $appName, private readonly string $environmentName)
    {
    }
    #[Route('/status', name: 'api_status')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $status = 'OK';

        try {
            // To force connect to DB.
            $entityManager->getConnection()->getNativeConnection();
            $connectionDb = $entityManager->getConnection()->isConnected() ? 'OK' : 'NA';
        } catch (Throwable $throwable) {
            $connectionDb = $throwable->getMessage();
            $status = 'No connection to DB';
        }

        return $this->json(
            [
                'status' => $status,
                'appName' => $this->appName,
                'environmentName' => $this->environmentName,
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[APP_ENV](aka Symfony app mode)' => ($_ENV['APP_ENV'] ?? 'NA'),
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[DATABASE_NAME]' => ($_ENV['DATABASE_NAME'] ?? 'NA'),
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[DATABASE_HOST]' => ($_ENV['DATABASE_HOST'] ?? 'NA'),
                'php.ini file used' => $this->getPhpIniFileVersion(),
                'connectionToDb' => $connectionDb,
                'opcacheStatistics' => $this->getPreloadStatistics(),
            ],
        );
    }

    private function getPhpIniFileVersion(): string
    {
        $version = 'NA';

        $phpIniFile = file_get_contents('/usr/local/etc/php/php.ini');

        if ($phpIniFile === false) {
            return $version;
        }

        if (str_contains($phpIniFile, 'This is the php.ini-production INI file.')) {
            $version = 'php.ini-production';
        }

        if (str_contains($phpIniFile, 'This is the php.ini-development INI file.')) {
            $version = 'php.ini-development';
        }

        return $version;
    }

    /** @return array<string, int|string> */
    private function getPreloadStatistics(): array
    {
        $opcacheStatistics = [
            'opcacheMemoryUsage' => 'NA',
            'preloadingFile' => 'NA',
            'isPreloadingUsed' => 'NO',
        ];

        $file = dirname(__DIR__) . '/../var/cache/prod/App_KernelProdContainer.preload.php';

        if (file_exists($file)) {
            $opcacheStatistics['preloadingFile'] = $file;
        }

        $opcacheStatus = opcache_get_status();

        if ($opcacheStatus === false) {
            return $opcacheStatistics;
        }

        $opcacheStatistics['opcacheMemoryUsage'] = $this->getOpcacheMemoryUsage($opcacheStatus);

        if (!array_key_exists('preload_statistics', $opcacheStatus)) {
            return $opcacheStatistics;
        }

        $preloadStatistics = $opcacheStatus['preload_statistics'];

        unset($opcacheStatistics['isPreloadingUsed']);

        $opcacheStatistics['preloadedFunctions'] = count($preloadStatistics['functions'] ?? []);
        $opcacheStatistics['preloadedClasses'] = count($preloadStatistics['classes'] ?? []);
        $opcacheStatistics['preloadedScripts'] = count($preloadStatistics['scripts'] ?? []);
        $opcacheStatistics['preloadingMemoryConsumption']
            = round($preloadStatistics['memory_consumption'] / 1_000_000, 2) . 'MB';

        return $opcacheStatistics;
    }

    /** @param array<string, array<string, int>> $opcacheStatus */
    private function getOpcacheMemoryUsage(array $opcacheStatus): string
    {
        if (array_key_exists('memory_usage', $opcacheStatus)
            && array_key_exists('used_memory', $opcacheStatus['memory_usage'])
            && array_key_exists('free_memory', $opcacheStatus['memory_usage'])
        ) {
            $totalMemory = $opcacheStatus['memory_usage']['used_memory'] + $opcacheStatus['memory_usage']['free_memory'];

            return sprintf(
                '%s MB used out of %s MB (%s%%). %s MB wasted.',
                round($opcacheStatus['memory_usage']['used_memory'] / (1024 * 1024)),
                round($totalMemory / (1024 * 1024)),
                round($opcacheStatus['memory_usage']['used_memory'] / $totalMemory * 100),
                round(($opcacheStatus['memory_usage']['wasted_memory'] ?? 0) / (1024 * 1024), 2),
            );
        }

        return 'NA';
    }
}
