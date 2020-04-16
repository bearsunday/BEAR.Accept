<?php

declare(strict_types=1);

namespace BEAR\Accept;

use Aura\Accept\AcceptFactory;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Named;
use Ray\Di\InjectorInterface;

final class AcceptRenderer implements RenderInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var array
     */
    private $availableMediaType;

    /**
     * @Named(availableMediaType=available_media_type)
     */
    public function __construct(InjectorInterface $injector, array $availableMediaType)
    {
        $this->injector = $injector;
        $this->availableMediaType = $availableMediaType;
    }

    public function render(ResourceObject $ro) : string
    {
        $accept = (new AcceptFactory($_SERVER))->newInstance();
        $media = $accept->negotiateMedia($this->availableMediaType);
        $mediaAnnotation = $media ? $media->getValue() : '';
        $renderer = $this->injector->getInstance(RenderInterface::class, $mediaAnnotation);
        $ro->headers['Vary'] = 'Accept';

        return $renderer->render($ro);
    }
}
