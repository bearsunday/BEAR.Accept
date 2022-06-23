<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Module\AppModule;
use BEAR\Accept\Resource\App\Foo;
use BEAR\AppMeta\Meta;
use BEAR\Package\Module\AppMetaModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function json_decode;
use function json_encode;

class AcceptInterceptorTest extends TestCase
{
    public function test2ndMatch(): Foo
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'hal-app',
                'application/json' => 'app',
                'text/html' => 'html-app',
                '*' => 'app', // default
            ],
        ];
        $injector = new Injector(new AcceptModule($available, new AppModule(new AppMetaModule(new Meta('BEAR\Accept')))), __DIR__ . '/tmp');
        $foo = $injector->getInstance(Foo::class);
        $_SERVER['HTTP_ACCEPT'] = 'application/json;q=1.0,text/html;q=1.5,*;q=0.1';
        assert($foo instanceof Foo);
        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('{"message":"hello"}', json_encode(json_decode($view)));

        return $foo;
    }

    /**
     * @depends test2ndMatch
     */
    public function test1stMatch(Foo $foo): void
    {
        $foo->view = null;
        $_SERVER['HTTP_ACCEPT'] = 'application/hal+json;q=1.0,text/html;q=1.5,*;q=0.1';
        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('fake-hal', $view);
        $this->assertSame('Accept', $foo->headers['Vary']);
    }

    /**
     * @depends test2ndMatch
     */
    public function testNoMatch(Foo $foo): void
    {
        $foo->view = null;
        $_SERVER['HTTP_ACCEPT'] = 'text/csv;q=1.5,*;q=0.1'; // no match
        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('{"message":"hello"}', json_encode(json_decode($view)));
    }
}
