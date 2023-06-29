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
use function str_contains;

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

        [$preloadingFile, $classesPreloaded] = $this->getPreloadInfo();

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
                'preloadingFile' => $preloadingFile,
                'classesPreloaded' => $classesPreloaded,
            ],
        );
    }

    /** @return array<int, int|string> */
    private function getPreloadInfo(): array
    {
        $preloadingFile = 'NA';
        $file = dirname(__DIR__) . '/../var/cache/prod/App_KernelProdContainer.preload.php';

        if (file_exists($file)) {
            $preloadingFile = $file;
        }

        $classesPreloaded = 'NA';

        $opcacheStatus = opcache_get_status();

        if ($opcacheStatus === false || !array_key_exists('preload_statistics', $opcacheStatus)) {
            return [$preloadingFile, $classesPreloaded];
        }

        $opcachePreloadStatistics = $opcacheStatus['preload_statistics'];

        if (array_key_exists('classes', $opcachePreloadStatistics)) {
            $classesPreloaded = (string) (count($opcachePreloadStatistics['classes']));
        }

        return [$preloadingFile, $classesPreloaded];
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
