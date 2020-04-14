<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\AppInjector;
use BEAR\Resource\RenderInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

final class AcceptInterceptor implements MethodInterceptor
{
    /**
     * @var array
     */
    private $available;

    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    /**
     * @Available("available")
     */
    public function __construct(array $available, AbstractAppMeta $appMeta)
    {
        $this->available = $available;
        $this->appMeta = $appMeta;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $produce = $invocation->getMethod()->getAnnotation(Produces::class);
        /* @var $produce \BEAR\Accept\Annotation\Produces */
        $accept = $this->getAccept($this->available['Accept'], $produce->value);
        $accept = new Accept(['Accept' => $accept]);
        list($context, $vary) = $accept->__invoke($_SERVER);
        $renderer = (new AppInjector($this->appMeta->name, $context))->getInstance(RenderInterface::class);
        $ro = $invocation->getThis();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $ro->setRenderer($renderer);
        $ro = $invocation->proceed();
        $ro->headers['Vary'] = $vary;

        return $ro;
    }

    private function getAccept(array $default, array $produces) : array
    {
        $accept = [];
        foreach ($produces as $produce) {
            if (isset($default[$produce])) {
                $accept[$produce] = $default[$produce];
            }
        }
        $accept['*'] = $default['*'];

        return $accept;
    }
}
