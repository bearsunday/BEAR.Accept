<?php

declare(strict_types=1);

namespace BEAR\Accept;

interface AcceptInterface
{
    /**
     * Return context string
     */
    public function __invoke(array $server) : array;
}
