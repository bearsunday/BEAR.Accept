<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class Available
{
    public function __construct(
        public readonly string $value = '',
    ) {
    }
}
