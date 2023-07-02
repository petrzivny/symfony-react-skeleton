<?php

declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php')) {
    include dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';
}

if (file_exists(dirname(__DIR__) . '/.env.local.php')) {
    opcache_compile_file(dirname(__DIR__) . '/.env.local.php');
}
