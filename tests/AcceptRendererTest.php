<?php

namespace BEAR\Accept;

use BEAR\Accept\Module\AppModule;
use BEAR\Accept\Resource\App\Foo;
use BEAR\AppMeta\Meta;
use BEAR\Package\AppMetaModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AcceptRendererTest extends TestCase
{
    /**
     * @var AcceptRenderer
     */
    private $acceptRender;

    public function setUp(): void
    {
        parent::setUp();
        $this->acceptRender = new AcceptRenderer;
    }

    public function testInnstaceOf()
    {
        $this->assertInstanceOf(AcceptRenderer::class, $this->acceptRender);
    }

    public function testRender()
    {
        $available = [
            'Accept' => [
                'application/hal+json' => 'hal-app',
                'application/json' => 'app',
                'text/html' => 'html-app',
                '*' => 'app' // default
            ]
        ];
        $injector = new Injector(new AppModule(new AppMetaModule(new Meta('BEAR\Accept'))), __DIR__ . '/tmp');
        $foo = $injector->getInstance(Foo::class);
        assert($foo instanceof Foo);
        $_SERVER['HTTP_ACCEPT'] = 'application/hal+json;q=1.0,text/html;q=1.5,*;q=0.1';
        $foo->setRenderer($this->acceptRender);
        $foo->onGet();
        $view = (string) $foo;
        $this->assertSame('fake-hal', $view);
        $this->assertSame('Accept', $foo->headers['Vary']);
    }
}
