<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
if (array_key_exists('APP_ENV', $_SERVER) === false) {
    throw new RuntimeException('APP_ENV env variable is not defined');
}

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
$appEnv = $_SERVER['APP_ENV'];

if (!is_string($appEnv)) {
    throw new RuntimeException('$_SERVER[APP_ENV] must be string');
}

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
if (array_key_exists('APP_DEBUG', $_SERVER) === false) {
    throw new RuntimeException('APP_DEBUG env variable is not defined');
}

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
$appDebug = $_SERVER['APP_DEBUG'];

$kernel = new Kernel($appEnv, (bool) $appDebug);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
assert($doctrine instanceof Registry);

return $doctrine->getManager();
