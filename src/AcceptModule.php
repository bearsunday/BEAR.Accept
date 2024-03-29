<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use Ray\Di\AbstractModule;

final class AcceptModule extends AbstractModule
{
    /** @var array */
    private $available;

    /**
     * @param array<string, array<string, string>> $available
     */
    public function __construct(array $available, ?AbstractModule $module = null)
    {
        $this->available = $available;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(Available::class)->toInstance($this->available);
        $this->bind(AcceptInterface::class)->to(Accept::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Produces::class),
            [AcceptInterceptor::class]
        );
    }
}
