<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Selects the representation of the method from the request Accept header
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/content-negotiation.html#produces-attribute
 * @see \BEAR\Accept\AcceptInterceptor
 */
#[Attribute(Attribute::TARGET_METHOD)]
#[Qualifier]
final class Produces
{
    /**
     * @param array<string> $value Media types the method produces, highest priority first;
     *                             types absent from the `Accept` map of #[Available] are ignored
     */
    public function __construct(
        public readonly array $value,
    ) {
    }
}
