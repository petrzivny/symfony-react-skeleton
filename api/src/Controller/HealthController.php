<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use function array_key_exists;
use function dirname;
use function file_exists;
use function getenv;
use function opcache_get_status;

/** @psalm-api */
final class HealthController extends AbstractController
{
    #[Route('/health', name: 'api_health')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        // to force connect to DB
        $entityManager->getConnection()->getNativeConnection();

        [$preloadingFile, $classesPreloaded] = $this->getPreloadInfo();

        return $this->json([
            'status' => 'OK',
            // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
            '$_ENV[APP_ENV]' => $_ENV['APP_ENV'] ?? 'NA',
            'getenv(APP_ENV)' => getenv('APP_ENV') ?: 'NA',
            // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
            '$_ENV[DATABASE_HOST]' => $_ENV['DATABASE_HOST'] ?? 'NA',
            'connection to DB' => $entityManager->getConnection()->isConnected() ? 'OK' : 'NA',
            'preloadingFile' => $preloadingFile,
            'classesPreloaded' => $classesPreloaded,
        ]);
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
            $classesPreloaded = count($opcachePreloadStatistics['classes']);
        }

        return [$preloadingFile, $classesPreloaded];
    }
}
