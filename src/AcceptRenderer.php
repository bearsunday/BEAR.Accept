<?php

declare(strict_types=1);

namespace BEAR\Accept;

use Aura\Accept\Accept as AuraAccept;
use Aura\Accept\AcceptFactory;
use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Exception\InvalidContextKeyException;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

final class AcceptRenderer implements RenderInterface
{
    public function render(ResourceObject $ro) : string
    {
        return '';
    }
}
