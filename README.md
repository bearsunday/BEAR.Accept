# BEAR.Accept

Provides content-negotiation using Accept* headers for [BEAR.Sunday](http://bearsunday.github.io/)


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
                'application/hal+json' => 'prod-hal-app',
                'application/json' => 'prod-app',
                'text/csv' => 'prod-csv-app',
                'cli' => 'prod-html-app'
            ],
            'Accept-Language' => [
                'en-US' => 'en',
                'ja-JP' => 'ja'
            ]
        ];
        // $available support 'Accept' and 'Accept-Language' key only
        $this->install(new AcceptModule($available));
    }
}
```

`Accept` specifies all of the available media in the format `[$mediatype => $context]`. `cli` is the context in case of console access. The renderer of the context of the media type matched by content negotiation is used for rendering the resource.

`Accept-Language` specifies all available languages in the format `[$lang => $contextKey]`. 

For example, if `application/hal+json` and `ja-JP`matches, the `$context` is `prod-hal-jp-app`. (We set `JpModule` in `App\Module` folder and bind it for Japanese.)

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

**application/json** and **text/csv** media type is available for this resource.　The `Vary` header is added automatically.

## To All

To perform content negotiation on all resources, prepare a special bootstrap file. This is especially useful when negotiating languages.

cn.php

```php
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

Prepare the module of the DI setting necessary for each language.

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