<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

final class AcceptRendererModule extends AbstractModule
{
    /**
     * @var array
     */
    private $available;

    public function __construct(array $available, AbstractModule $module = null)
    {
        $this->available = $available;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        foreach ($this->available as $mediaType => $renderer) {
            $this->bind(RenderInterface::class)->annotatedWith($mediaType)->to($renderer);
        }
        $this->bind()->annotatedWith('available_media_type')->toInstance(array_keys($this->available));
    }
}
