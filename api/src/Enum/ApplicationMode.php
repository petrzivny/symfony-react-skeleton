<?php

declare(strict_types=1);

namespace App\Enum;

enum ApplicationMode: string
{
    case Development = 'dev';
    case Production = 'prod';
}
