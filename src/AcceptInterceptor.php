<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\AppInjector;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function assert;

final class AcceptInterceptor implements MethodInterceptor
{
    /** @var array<string, array<string, string>> */
    private $available;

    /** @var AbstractAppMeta */
    private $appMeta;

    /**
     * @param array<string, array<string, string>> $available
     *
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
    public function invoke(MethodInvocation $invocation): ResourceObject
    {
        $produce = $invocation->getMethod()->getAnnotation(Produces::class);
        assert($produce instanceof Produces);
        $accept = $this->getAccept($this->available['Accept'], $produce->value);
        $accept = new Accept(['Accept' => $accept]);
        [$context, $vary] = $accept->__invoke($_SERVER);
        $renderer = (new AppInjector($this->appMeta->name, $context))->getInstance(RenderInterface::class);
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $ro->setRenderer($renderer);
        $ro = $invocation->proceed();
        $ro->headers['Vary'] = $vary;

        return $ro;
    }

    private function getAccept(array $default, array $produces): array
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
