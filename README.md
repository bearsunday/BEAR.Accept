# BEAR.Accept

Provides content-negotiation tools using Accept* headers for BEAR.Sunday framework.

## Composer install

    $ composer require bear/accept
 
## Module install


```php

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $available = [
            'Accept' => [
                'application/json+hal' => 'prod-hal-app',
                'application/json' => 'prod-app',
                'text/csv' => 'prod-csv-app',
                '*' => 'prod-html-app'
            ],
            'Accept-Language' => [
                'en-US' => 'en',
                'ja-JP' => 'ja',
                '*' => 'ja',
            ]
        ];
        // $available support 'Accept' and 'Accept-Language' key only
        $this->install(new AcceptModule($available));
    }
}
```

# Usage

## By Resource

Annotate the resource to do content negotiation with `@Produces`.

```php
use use BEAR\Accept\Annotation\Produces;

/**
 * @Produces({"application/json", "text/csv"})
 */
public function onGet()
```

**application/json** and **text/csv** media type is available for this resource.

## To All

To perform content negotiation on all resources, prepare a special bootstrap file. This is especially useful when negotiating languages.

cn.php

```
require dirname(__DIR__) . '/vendor/autoload.php';

$available = [
    'Accept' => [
        'text/html' => 'prod-html-app',
        'application/hal+json' => 'prod-hal-app',
        'application/json' => 'prod-app',
        '*' => 'prod-html-app'
    ],
    'Accept-Language' => [
        'ja' => 'ja',
        'en-US' => 'us',
        '*' => 'us'
    ]
];
$accept = new \BEAR\Accept\Accept($available);
list($context, $vary) = $accept($_SERVER);

require __DIR__ . '/bootstrap.php';
```

Add a vary header in `bootstrap.php` to enable caching when using content negotiation.

```php
+    /* @global \BEAR\Resource\Vary $vary */
+    if (isset($vary)) {
+        $page->headers['Vary'] = $vary;
+    }
     $page->transfer($app->responder, $_SERVER);
```

Prepare the module of the DI setting necessary for each language

```php
use BEAR\Sunday\Module\Constant\NamedModule;

class JaModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $text = ['greeting' => 'こんにちは'];
        $this->install(new NamedModule($text));
    }
}
```

# Requirements

 * php7.0+