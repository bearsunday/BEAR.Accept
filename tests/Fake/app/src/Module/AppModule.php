<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept\Module;

use BEAR\Resource\JsonRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(RenderInterface::class)->to(JsonRenderer::class)->in(Scope::SINGLETON);
    }
}
