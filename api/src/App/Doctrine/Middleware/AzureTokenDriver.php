<?php

declare(strict_types = 1);
namespace App\Doctrine\Middleware;

use App\Azure\AzurePostgresTokenProvider;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

final class AzureTokenDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $wrapped,
        private readonly AzurePostgresTokenProvider $tokens,
    ) {
        parent::__construct($wrapped);
    }

    public function connect(#[\SensitiveParameter] array $params): DriverConnection
    {
        if (empty($params['password'])) {
            $params['password'] = $this->tokens->getToken();
        }

        return parent::connect($params);
    }
}
