<?php

declare(strict_types=1);

namespace Tests\App\Functional;

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

    public static function urlProvider(): Generator
    {
        yield ['/status'];
        // ...
    }
}
