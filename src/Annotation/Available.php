<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier()
 */
final class Available
{
    public $value;
}
