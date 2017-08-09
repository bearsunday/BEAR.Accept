<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept;

use BEAR\Accept\Exception\InvalidContextKeyException;
use BEAR\Accept\Exception\NoAsteriskMediaTypeException;
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
                'text/csv' => 'prod-csv-app',
                '*' => 'prod-html-app'            // when nothing match
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual, $vary) = $accept->__invoke($server);
        $this->assertSame('prod-csv-app', $actual);
        $this->assertSame('Accept', (string) $vary);
    }

    public function testNoMatchUseDefault()
    {
        $available = [
            'Accept' => [
                'application/json' => 'prod-app',
                '*' => 'prod-html-app'            // when nothing match
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/xml;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-html-app', $actual);
    }

    public function testJsonPlusHal()
    {
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
                'application/json' => 'prod-app',
                '*' => 'prod-html-app'            // when nothing match
            ]
        ];
        $accept = new Accept($available);
        $server['HTTP_ACCEPT'] = 'application/json+hal;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-hal-app', $actual);
        $server['HTTP_ACCEPT'] = 'application/json;q=1.0,text/csv;q=0.5,*;q=0.1';
        list($actual) = $accept->__invoke($server);
        $this->assertSame('prod-app', $actual);
    }

    public function testInvalidKey()
    {
        $this->expectException(InvalidContextKeyException::class);
        $context = ['Invalid' => []];
        $this->accept = new Accept($context);
    }

    public function testLang()
    {
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
                'application/json' => 'prod-app',
                'text/html' => 'prod-html-app',
                '*' => 'prod-html-app'
            ],
            'Accept-Language' => [
                'en-US' => 'en',
                'ja-JP' => 'ja',
                '*' => 'ja',
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

    public function testNoDefault()
    {
        $this->expectException(NoAsteriskMediaTypeException::class);
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
            ]
        ];
        new Accept($available);
    }
}
