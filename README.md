# Symfony Messenger Bundle
The `SymfonyMessengerBundle` offers a suite of middleware extensions designed to enhance the functionality of the Symfony Messenger component.

## Installation
Use Composer to install the bundle:
```
composer require macpaw/symfony-messenger-bundle
```

## Applications that don't use Symfony Flex
Enable the bundle by adding it to the list of registered bundles in ```config/bundles.php```

```
// config/bundles.php
<?php

return [
            Macpaw\SymfonyMessengerBundle\SymfonyMessengerBundle::class => ['all' => true],
        // ...
    ];
```

## Add middlewares to messenger component

See https://symfony.com/doc/current/messenger.html#messenger_middleware

```config/packages/messenger.yaml```

Example:

```
# config/packages/messenger.yaml
framework:
    messenger:
        buses:
            messenger.bus.default:
                # disable the default middleware
                default_middleware: false

                middleware:
                    # use and configure parts of the default middleware you want
                    - 'add_bus_name_stamp_middleware': ['messenger.bus.default']

                    # add your own services that implement Symfony\Component\Messenger\Middleware\MiddlewareInterface
                    - 'Macpaw\SymfonyMessengerBundle\Middleware\DoctrineTransactionMiddleware'
```


