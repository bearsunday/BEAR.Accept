<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Exception\InvalidContextKeyException;
use LogicException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    public function testAccepted(): void
    {
        $available = [
            'Accept' => [
                'application/json' => 'prod-app',
                'text/csv' => 'prod-csv-app',
            ],
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,text/csv;q=0.5,*;q=0.1';
        [$actual, $vary] = $accept->__invoke($server);
        $this->assertSame('prod-csv-app', $actual);
        $this->assertSame('Accept', (string) $vary);
    }

    public function testNoMatchUseFirstDefault(): void
    {
        $available = [
            'Accept' => [
                'application/ha+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app',
            ],
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,*;q=0.1';
        [$actual] = $accept->__invoke($server);
        $this->assertSame('prod-hal-api-app', $actual);
    }

    public function testMatchPriority(): void
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app',
                'cli' => 'cli-hal-api-app',                   // CLI
            ],
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/hal+json;q=1.0,text/csv;q=0.5,*;q=0.1';
        [$actual] = $accept->__invoke($server);
        $this->assertSame('prod-hal-api-app', $actual);
        $server['HTTP_ACCEPT'] = 'application/json;q=1.0,text/csv;q=0.5,*;q=0.1';
        [$actual] = $accept->__invoke($server);
        $this->assertSame('prod-api-app', $actual);
    }

    public function testInvalidKey(): void
    {
        $this->expectException(InvalidContextKeyException::class);
        $available = ['Invalid' => []];
        $this->accept = new Accept($available);
    }

    public function testCli(): void
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app',
                'cli' => 'cli-hal-api-app',                   // CLI
            ],
        ];
        $accept = new Accept($available);
        $server = [];
        [$actual, $vary] = $accept->__invoke($server);
        $this->assertSame('cli-hal-api-app', $actual);
        $this->assertSame('Accept', (string) $vary);
    }

    public function testLang(): Accept
    {
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
                'application/json' => 'prod-app',
                'text/html' => 'prod-html-app',
                'cli' => 'prod-html-app',
            ],
            'Accept-Language' => [
                'ja-JP' => 'ja',
                'en-US' => 'en',
            ],
        ];
        $accept = new Accept($available);
        $server = [
            'HTTP_ACCEPT' => 'application/json+hal;q=1.0,application/json;q=0.5,*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US, en-GB, en, *',
        ];
        [$actual, $vary] = $accept->__invoke($server);
        $this->assertSame('prod-hal-en-app', $actual);
        $this->assertSame('Accept, Accept-Language', (string) $vary);

        return $accept;
    }

    #[Depends('testLang')]
    public function testLangJp(Accept $accept): void
    {
        $server = [
            'HTTP_ACCEPT' => 'application/json+hal;q=1.0,application/json;q=0.5,*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'ja-JP, en-US, en-GB, en, *',
        ];
        [$actual, $vary] = $accept->__invoke($server);
        $this->assertSame('prod-hal-ja-app', $actual);
        $this->assertSame('Accept, Accept-Language', (string) $vary);
    }

    public function testLangNoMatch(): void
    {
        $this->expectException(LogicException::class);
        $available = [
            'Accept' => ['application/json' => 'prod-app'],
            'Accept-Language' => ['ja-JP' => 'ja'],
        ];
        $accept = new Accept($available);
        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR',
        ];
        $accept->__invoke($server);
    }
}
