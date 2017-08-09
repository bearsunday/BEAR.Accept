<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept\Resource\App;

use BEAR\Accept\Annotation\Produces;
use BEAR\Resource\ResourceObject;

class Foo extends ResourceObject
{
    public $body = ['message' => 'hello'];

    /**
     * @Produces({"text/csv", "application/json", "application/hal+json"})
     */
    public function onGet()
    {
        return $this;
    }
}
