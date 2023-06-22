<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function array_key_exists;
use function header;
use function sprintf;

/** @psalm-api */
final class TestController extends AbstractController
{
    private const SUB_FEATURE_NAME = 'UPDATE privilege at the table level';
    private const COLUMN_NAME = 'is_supported';

    private const APP_DEBUG_VARIABLE_NAME = 'APP_DEBUG';
    private const APP_ENV_VARIABLE_NAME = 'APP_ENV';

    #[Route('/test-get-db-value', name: 'app_test')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        // phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
        if (array_key_exists(self::APP_DEBUG_VARIABLE_NAME, $_ENV)
            && array_key_exists(self::APP_ENV_VARIABLE_NAME, $_ENV)
            && $_ENV[self::APP_DEBUG_VARIABLE_NAME] === '1'
            && $_ENV[self::APP_ENV_VARIABLE_NAME] === 'dev'
            // phpcs:enable
        ) {
            header('Access-Control-Allow-Origin: *');
        }

        $connection = $entityManager->getConnection();
        $sql = sprintf(
            "SELECT %s FROM information_schema.sql_features WHERE sub_feature_name = '%s'",
            self::COLUMN_NAME,
            self::SUB_FEATURE_NAME,
        );

        try {
            $stmt = $connection->prepare($sql);
            $result = $stmt->executeQuery();
            $responseData = ['data' => ['optionName' => self::SUB_FEATURE_NAME, 'isSupported' => $result->fetchOne()]];
        } catch (Exception $exception) {
            return new JsonResponse(
                ['error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]],
                503,
            );
        }

        return new JsonResponse($responseData);
    }


    #[Route('/test-logger', name: 'test_logger')]
    public function test(LoggerInterface $logger): Response
    {
        $logger->debug('DEBUG message');
        $logger->info('INFO message');
        $logger->notice('NOTICE message');
        $logger->warning('WARNING message');
        $logger->error('ERROR message');
        $logger->critical('CRITICAL message');
        $logger->alert('ALERT message');
        $logger->emergency('EMERGENCY message');

        return new Response('See logs for 7 custom log entries and check correct severities.');
    }
}
