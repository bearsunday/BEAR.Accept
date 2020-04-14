<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Module\AppModule;
use BEAR\Accept\Resource\App\Foo;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\AppMetaModule;
use BEAR\Resource\ResourceObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AcceptInterceptorTest extends TestCase
{
    public function test2ndMatch()
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'hal-app',
                'application/json' => 'app',
                'text/html' => 'html-app',
                '*' => 'app' // default
            ]
        ];
        $injector = new Injector(new AcceptModule($available, new AppModule(new AppMetaModule(new AppMeta('BEAR\Accept')))), __DIR__ . '/tmp');
        $foo = $injector->getInstance(Foo::class);
        $_SERVER['HTTP_ACCEPT'] = 'application/json;q=1.0,text/html;q=1.5,*;q=0.1';

        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('{"message":"hello"}', $view);

        return $foo;
    }

    /**
     * @depends test2ndMatch
     */
    public function test1stMatch(ResourceObject $foo)
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
    public function testNoMatch(ResourceObject $foo)
    {
        $foo->view = null;
        $_SERVER['HTTP_ACCEPT'] = 'text/csv;q=1.5,*;q=0.1'; // no match
        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('{"message":"hello"}', $view);
    }
}
