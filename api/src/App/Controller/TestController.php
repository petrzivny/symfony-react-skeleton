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

use function sprintf;

/** @psalm-api */
final class TestController extends AbstractController
{
    private const string SUB_FEATURE_NAME = 'UPDATE privilege at the table level';
    private const string COLUMN_NAME = 'is_supported';

    #[Route('/test-get-db-value', name: 'app_test')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
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
