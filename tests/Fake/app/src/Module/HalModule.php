<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept\Module;

use BEAR\Accept\FakeHalRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class HalModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(RenderInterface::class)->to(FakeHalRenderer::class)->in(Scope::SINGLETON);
    }
}
