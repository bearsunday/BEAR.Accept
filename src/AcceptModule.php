<?php
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use Ray\Di\AbstractModule;

final class AcceptModule extends AbstractModule
{
    /**
     * @var array
     */
    private $available;

    public function __construct(array $available, AbstractModule $module = null)
    {
        $this->available = $available;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
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
