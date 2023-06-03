<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    include dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
if (array_key_exists('APP_DEBUG', $_SERVER) && $_SERVER['APP_DEBUG']) {
    umask(0000);
}
