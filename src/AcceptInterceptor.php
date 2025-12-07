<?php

declare(strict_types=1);

namespace BEAR\Accept;

use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Annotation\Produces;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function array_filter;
use function assert;
use function is_string;

use const ARRAY_FILTER_USE_BOTH;

final readonly class AcceptInterceptor implements MethodInterceptor
{
    /** @param array<string, array<string, string>> $available */
    public function __construct(
        #[Available('available')]
        private array $available,
        private AbstractAppMeta $appMeta,
    ) {
    }

    /** {@inheritDoc} */
    public function invoke(MethodInvocation $invocation): ResourceObject
    {
        $produce = $invocation->getMethod()->getAnnotation(Produces::class);
        assert($produce instanceof Produces);
        $accept = $this->getAccept($this->available['Accept'], $produce->value);
        $accept = new Accept(['Accept' => $accept]);
        /** @var array<string, string> $server */
        $server = array_filter($_SERVER, static fn ($v, $k): bool => is_string($k) && is_string($v), ARRAY_FILTER_USE_BOTH);
        [$context, $vary] = $accept->__invoke($server);
        assert($context !== '');
        $renderer = Injector::getInstance($this->appMeta->name, $context, $this->appMeta->appDir)->getInstance(RenderInterface::class);
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $ro->setRenderer($renderer);
        /** @var ResourceObject $ro */
        $ro = $invocation->proceed();
        $ro->headers['Vary'] = $vary;

        return $ro;
    }

    /**
     * @param array<string, string> $default
     * @param array<string>         $produces
     *
     * @return array<string, string>
     */
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
