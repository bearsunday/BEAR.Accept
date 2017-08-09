<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeHalRenderer implements RenderInterface
{
    public function render(ResourceObject $resourceObject)
    {
        $resourceObject->view = 'fake-hal';

        return $resourceObject->view;
    }
}
