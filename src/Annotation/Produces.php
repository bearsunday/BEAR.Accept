<?php

declare(strict_types=1);

namespace BEAR\Accept\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier()
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD), Qualifier]
final class Produces
{
    /** @var array<string> */
    public $value;

    /**
     * @param array<string> $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}
