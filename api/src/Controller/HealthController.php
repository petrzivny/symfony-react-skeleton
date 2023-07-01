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
use function getenv;
use function opcache_get_status;
use function round;
use function str_contains;
use function var_dump;

/** @psalm-api */
final class HealthController extends AbstractController
{
    #[Route('/health', name: 'api_health')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // To force connect to DB.
            $entityManager->getConnection()->getNativeConnection();
            $connectionDb = $entityManager->getConnection()->isConnected() ? 'OK' : 'NA';
        } catch (Throwable $throwable) {
            $connectionDb = $throwable->getMessage();
        }

        return $this->json(
            [
                'status' => 'OK',
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[APP_ENV]' => ($_ENV['APP_ENV'] ?? 'NA'),
                'getenv(APP_ENV)' => getenv('APP_ENV') ?: 'NA',
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[DATABASE_NAME]' => ($_ENV['DATABASE_NAME'] ?? 'NA'),
                'getenv(DATABASE_NAME)' => getenv('DATABASE_NAME') ?: 'NA',
                // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
                '$_ENV[DATABASE_HOST]' => ($_ENV['DATABASE_HOST'] ?? 'NA'),
                'getenv(DATABASE_HOST)' => getenv('DATABASE_HOST') ?: 'NA',
                'php.ini file used' => $this->getPhpIniFileVersion(),
                'connectionToDb' => $connectionDb,
                'preloadStatistics' => $this->getPreloadStatistics(),
            ],
        );
    }

    /** @return array<string, int|string> */
    private function getPreloadStatistics(): array
    {
        $preloadStatistics = [
            'preloadingFile' => 'NA',
            'isOperational' => 'NO',
        ];

        $file = dirname(__DIR__) . '/../var/cache/prod/App_KernelProdContainer.preload.php';

        if (file_exists($file)) {
            $preloadStatistics['preloadingFile'] = $file;
        }

        $opcacheStatus = opcache_get_status();

        if ($opcacheStatus === false || !array_key_exists('preload_statistics', $opcacheStatus)) {
            return $preloadStatistics;
        }

        $statistics = $opcacheStatus['preload_statistics'];
        // @phpstan-ignore-next-line
        var_dump($statistics);
        die;

        // @phpstan-ignore-next-line
        unset($preloadStatistics['isOperational']);

        $preloadStatistics['functions'] = count($statistics['functions']);
        $preloadStatistics['classes'] = count($statistics['classes']);
        $preloadStatistics['scripts'] = count($statistics['scripts']);
        $preloadStatistics['memoryConsumption'] = round($statistics['memory_consumption'] / 1_000_000, 2) . 'MB';

        return $preloadStatistics;
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
}
