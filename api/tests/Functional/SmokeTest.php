<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SmokeTest extends WebTestCase
{
    /** @dataProvider urlProvider */
    public function testPageIsSuccessful(string $url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    public function urlProvider(): Generator
    {
        yield ['/health'];
        // ...
    }
}
