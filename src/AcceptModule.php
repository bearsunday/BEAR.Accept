<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use Ray\Di\AbstractModule;

final class AcceptModule extends AbstractModule
{
    /** @param array<string, array<string, string>> $available */
    public function __construct(
        private readonly array $available,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    /** {@inheritDoc} */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(Available::class)->toInstance($this->available);
        $this->bind(AcceptInterface::class)->to(Accept::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Produces::class),
            [AcceptInterceptor::class],
        );
    }
}
