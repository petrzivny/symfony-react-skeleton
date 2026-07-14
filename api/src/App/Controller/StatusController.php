<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

use function array_key_exists;
use function count;
use function dirname;
use function file_exists;
use function file_get_contents;
use function opcache_get_status;
use function round;
use function sprintf;
use function str_contains;

final class StatusController extends AbstractController
{
    public function __construct(
        private readonly string $appName,
        private readonly string $environmentName,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $params,
    ) {
    }

    #[Route('/status', name: 'api_status')]
    public function index(): JsonResponse
    {
        return $this->json($this->constructResponsePayload());
    }

    /** @return array<string, string|array<string, int|string>> */
    public function constructResponsePayload(): array
    {
        [$status, $connectionDb] = $this->resolveDatabaseStatus();

        return array_merge(
            $this->buildCoreStatus($status, $connectionDb),
            $this->buildDatabaseEnvDiagnostics(),
            $this->buildAzureEnvDiagnostics(),
            $this->buildIdentityEnvDiagnostics(),
        );
    }

    /** @return array{0: string, 1: string} */
    private function resolveDatabaseStatus(): array
    {
        $status = 'OK';

        try {
            // To force connect to DB.
            $this->entityManager->getConnection()->getNativeConnection();
            $connectionDb = $this->entityManager->getConnection()->isConnected() ? 'OK' : 'NA';
        } catch (Throwable $throwable) {
            $connectionDb = $throwable->getMessage();
            $status = 'No connection to DB';
        }

        return [$status, $connectionDb];
    }

    /** @return array<string, string|array<string, int|string>> */
    private function buildCoreStatus(string $status, string $connectionDb): array
    {
        return [
            'status' => $status,
            'appName' => $this->appName,
            'environmentName' => $this->environmentName,
            '$_ENV[APP_ENV](aka Symfony app mode)' => $this->getRawEnv('APP_ENV'),
            'php.ini file used' => $this->getPhpIniFileVersion(),
            'connectionToDb' => $connectionDb,
            '$_SERVER[USER]' => $this->getServerUser(),
            'opcacheStatistics' => $this->getPreloadStatistics(),
        ];
    }

    /** @return array<string, string> */
    private function buildDatabaseEnvDiagnostics(): array
    {
        return array_merge(
            $this->envWithFile('DATABASE_NAME'),
            $this->envWithFile('DATABASE_HOST'),
            $this->envWithFile('DATABASE_USER'),
        );
    }

    /** @return array<string, string> */
    private function buildAzureEnvDiagnostics(): array
    {
        return $this->envWithFile('AZURE_POSTGRESQL_CLIENTID');
    }

    /** @return array<string, string> */
    private function buildIdentityEnvDiagnostics(): array
    {
        return [
            '$_ENV[IDENTITY_ENDPOINT]' => $this->getRawEnv('IDENTITY_ENDPOINT'),
            'env(IDENTITY_ENDPOINT)' => $this->getResolvedEnv('IDENTITY_ENDPOINT'),
            '$_ENV[IDENTITY_HEADER]' => $this->getRawEnv('IDENTITY_HEADER'),
            'env(IDENTITY_HEADER)' => $this->getResolvedEnv('IDENTITY_HEADER'),
        ];
    }

    /** @return array<string, string> */
    private function envWithFile(string $key): array
    {
        return [
            '$_ENV[' . $key . ']' => $this->getRawEnv($key),
            '$_ENV[' . $key . '_FILE]' => $this->getRawEnv($key . '_FILE'),
            'env(' . $key . ')' => $this->getResolvedEnv($key),
        ];
    }

    private function getResolvedEnv(string $key): string
    {
        $param = 'env(' . $key . ')';

        return $this->params->has($param) ? $this->params->get($param) : 'NA';
    }

    private function getRawEnv(string $key): string
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
        return $_ENV[$key] ?? 'NA';
    }

    private function getServerUser(): string
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
        return $_SERVER['USER'] ?? $_SERVER['HOME'] ?? 'NA';
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

        $file = dirname(__DIR__) . '/../../var/cache/prod/App_KernelProdContainer.preload.php';

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
                round($opcacheStatus['memory_usage']['used_memory'] / (1_024 * 1_024)),
                round($totalMemory / (1_024 * 1_024)),
                round($opcacheStatus['memory_usage']['used_memory'] / $totalMemory * 100),
                round(($opcacheStatus['memory_usage']['wasted_memory'] ?? 0) / (1_024 * 1_024), 2),
            );
        }

        return 'NA';
    }
}
