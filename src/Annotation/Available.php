<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the map of available media types and languages bound by AcceptModule
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class Available
{
}
