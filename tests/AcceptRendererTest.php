<?php

declare(strict_types=1);

namespace BEAR\Accept;

use function assert;
use BEAR\Accept\Module\AppModule;
use BEAR\Accept\Resource\App\Foo;
use BEAR\AppMeta\Meta;
use BEAR\Package\Module\AppMetaModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AcceptRendererTest extends TestCase
{
    /**
     * @var AcceptRenderer
     */
    private $acceptRender;

    /**
     * @var Foo
     */
    private $ro;

    protected function setUp() : void
    {
        parent::setUp();
        $availableRenderer = [
            'application/hal+json' => FakeHalRenderer::class,
            '' => FakeDeafultRenderer::class
        ];
        $this->acceptRender = new AcceptRenderer(
            new Injector(new AcceptRendererModule($availableRenderer)),
            array_keys($availableRenderer)
        );
        $injector = new Injector(new AppModule(new AppMetaModule(new Meta('BEAR\Accept'))), __DIR__ . '/tmp');
        $ro = $injector->getInstance(Foo::class);
        assert($ro instanceof Foo);
        $this->ro = $ro;
    }

    public function testInnstaceOf() : void
    {
        $this->assertInstanceOf(AcceptRenderer::class, $this->acceptRender);
    }

    public function testHalRender() : void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/hal+json;q=1.0,text/html;q=1.5,*;q=0.1';
        $this->ro->setRenderer($this->acceptRender);
        $this->ro->onGet();
        $view = (string) $this->ro;
        $this->assertSame('fake-hal', $view);
        $this->assertSame('Accept', $this->ro->headers['Vary']);
    }

    public function testDefaultRender() : void
    {
        $_SERVER['HTTP_ACCEPT'] = '';
        $this->ro->setRenderer($this->acceptRender);
        $this->ro->onGet();
        $view = (string) $this->ro;
        $this->assertSame('*', $view);
        $this->assertSame('Accept', $this->ro->headers['Vary']);
    }
}
