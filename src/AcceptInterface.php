<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept;

interface AcceptInterface
{
    /**
     * Return context string
     */
    public function __invoke(array $server) : array;
}
