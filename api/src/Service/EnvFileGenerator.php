<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

use function fclose;
use function fopen;
use function fwrite;

final class EnvFileGenerator
{
    /** @param array<string, string> $variables */
    public function storeEnvVariablesInFile(array $variables, string $file, string $environment): void
    {
        $fileResource = fopen($file, 'xb');

        if ($fileResource === false) {
            throw new RuntimeException('Unable to write to file ' . $file);
        }

        fwrite($fileResource, "# Secrets synced from external vault upon pod creation. \n");
        fwrite($fileResource, "# environment: $environment\n");
        fwrite($fileResource, "\n");

        foreach ($variables as $variableName => $variableValue) {
            fwrite($fileResource, $variableName . '=' . $variableValue . "\n");
        }

        fclose($fileResource);
    }
}
