<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept\Annotation;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier()
 */
class Produces
{
    /**
     * @var string[]
     */
    public $value;
}
