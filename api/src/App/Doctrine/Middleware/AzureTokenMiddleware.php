<?php

declare(strict_types=1);

namespace App\Doctrine\Middleware;

use App\Azure\AzurePostgresTokenProvider;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

#[AsMiddleware(priority: 100)]
final class AzureTokenMiddleware implements Middleware
{
    public function __construct(private readonly AzurePostgresTokenProvider $tokens)
    {
    }

    public function wrap(Driver $driver): Driver
    {
        return new AzureTokenDriver($driver, $this->tokens);
    }
}
