<?php

declare(strict_types=1);

namespace BEAR\Accept;

interface AcceptInterface
{
    /**
     * Return context string
     *
     * @param array<string, string> $server
     *
     * @return  array{string, string}
     */
    public function __invoke(array $server): array;
}
