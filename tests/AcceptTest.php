<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Exception\InvalidContextKeyException;
use PHPUnit\Framework\TestCase;

class AcceptTest extends TestCase
{
    /**
     * @var Accept
     */
    protected $accept;

    public function testAccepted()
    {
        $available = [
            'Accept' => [
                'application/json' => 'prod-app',
                'text/csv' => 'prod-csv-app'
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual, $vary) = $accept->__invoke($server);
        $this->assertSame('prod-csv-app', $actual);
        $this->assertSame('Accept', (string) $vary);
    }

    public function testNoMatchUseFirstDefault()
    {
        $available = [
            'Accept' => [
                'application/ha+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app'
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-hal-api-app', $actual);
    }

    public function testMatchPriority()
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app',
                'cli' => 'cli-hal-api-app'                   // CLI
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/hal+json;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-hal-api-app', $actual);
        $server['HTTP_ACCEPT'] = 'application/json;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-api-app', $actual);
    }

    public function testInvalidKey()
    {
        $this->expectException(InvalidContextKeyException::class);
        $available = ['Invalid' => []];
        $this->accept = new Accept($available);
    }

    public function testCli()
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'prod-hal-api-app',
                'application/json' => 'prod-api-app',
                'cli' => 'cli-hal-api-app'                   // CLI
            ]
        ];
        $accept = new Accept($available);
        $server = [];
        list($actual, $vary) = $accept->__invoke($server);
        $this->assertSame('cli-hal-api-app', $actual);
        $this->assertSame('Accept', (string) $vary);
    }

    public function testLang()
    {
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
                'application/json' => 'prod-app',
                'text/html' => 'prod-html-app',
                'cli' => 'prod-html-app'
            ],
            'Accept-Language' => [
                'ja-JP' => 'ja',
                'en-US' => 'en'
            ]
        ];
        $accept = new Accept($available);
        $server = [
            'HTTP_ACCEPT' => 'application/json+hal;q=1.0,application/json;q=0.5,*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US, en-GB, en, *'
        ];
        list($actual, $vary) = $accept->__invoke($server);
        $this->assertSame('prod-hal-en-app', $actual);
        $this->assertSame('Accept, Accept-Language', (string) $vary);

        return $accept;
    }

    /**
     * @depends testLang
     */
    public function testLangJp(Accept $accept)
    {
        $server = [
            'HTTP_ACCEPT' => 'application/json+hal;q=1.0,application/json;q=0.5,*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'ja-JP, en-US, en-GB, en, *'
        ];
        list($actual, $vary) = $accept->__invoke($server);
        $this->assertSame('prod-hal-ja-app', $actual);
        $this->assertSame('Accept, Accept-Language', (string) $vary);
    }
}
