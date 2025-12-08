<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD)]
#[Qualifier]
final class Produces
{
    /** @param array<string> $value */
    public function __construct(
        public readonly array $value,
    ) {
    }
}
