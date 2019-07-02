# Shopping Feed >signal< handler

Object oriented library over the pcntl PHP extension

# Installation

The library does not require the presence of `ext-pcntl`, but does not handle any signal when not installed

```
composer require shoppingfeed/signal
```

# Features

- Allow to register multiples callback on a single signal
- Allow to register one callback on multiples signals
- Allow manage previous handlers

# Signal Handler

The base object of the library is `ShoppingFeed\Signal\SignalHandler`

Basically, it's a wrapper around the [pcntl_signal](https://www.php.net/manual/fr/function.pcntl-signal.php) function,
and allow to register multiples callbacks per signal or restore previous installed handler if any.


The `ShoppingFeed\Signal\SignalHandler` object always handle a single signal

```php
<?php
namespace ShoppingFeed\Signal;

$handler = new SignalHandler(SIGINT);
```

Once the handler created, you're free to register callbacks on it. Note that unless you registered a callback,
the handler do not subscribe itself to the signal.

```php
<?php
namespace ShoppingFeed\Signal;

$handler = new SignalHandler(SIGINT);
$handler->append(function() {
    echo 'Graceful exit' . PHP_EOL;
    exit(0);
});


// Simulates the signal manually
$handler->handle(SIGINT); 
```

There is 2 way to add signal callbacks, considering they are invoked from top to bottom :

- `append`: push the callback at the end of the stack
- `prepend` : insert callback on the top of the stack

Both methods accept a set of options, which are:

- `once (bool)` : When true, the callback is only invoked one time, then removed from the signal handler instance
- `group (string)` : An arbitrary "group" name (see usage bellow)

You also have the possibility to "clear" (remove) all or groups of attached callbacks

```php
<?php
namespace ShoppingFeed\Signal;

$handler = new SignalHandler(SIGINT);
$handler->append($callback1, ['group' => 'group1']);
$handler->append($callback2, ['group' => 'group2']);
$handler->append($callback3, ['group' => 'group2', 'once' => true]);

// Clear all registered callbacks
$handler->clear();

// Or clear one or many callback given the associated group(s) name(s)`
$handler->clear(['group2']);
```

### Managing handlers

The pcntl extension does not supports multiples handlers per signal : every time you create a new SignalHandler and attach
callbacks, previous handler are overwritten and ignored by the extension.

To avoid conflicts, take care of :

- Use only one Signal Handler instance per signal
- Use appropriated flags (see bellow) when creating a new handler : it will explicit the expected behavior

### Handler flags

Optionally, the SignalHandler constructor accepts the following constants that describe how the handle should manage previous installed handlers

- SignalHandlerInterface::PREV_REPLACE : The default behavior. any previous installed handler will be overwritten 
- SignalHandlerInterface::PREV_RESTORE : Any previous installed handler will be re-installed once the SignalHandler callbacks stack becomes empty 
- SignalHandlerInterface::PREV_ERROR   : If previous installed handler, then the SignalHandler will throw a `ShoppingFeed\Signal\Exception\RuntimeException` exception when the first callback is registered

Example with `PREV_ERROR` :

```php
<?php
namespace ShoppingFeed\Signal;

$handler = new SignalHandler(SIGINT, SignalHandler::PREV_ERROR);

// An exception will be throw cause a handler is already registered
pcntl_signal(SIGINT, function() {});
$handler->append(function() {}); 
```

Example with `PREV_RESTORE` :

```php
<?php
namespace ShoppingFeed\Signal;

pcntl_signal(SIGALRM, function() { echo "Handler 1\n"; });

// Register a handler for one invocation only, unregistered after the first signal reception
$handler = new SignalHandler(SIGALRM, SignalHandler::PREV_RESTORE);
$handler->append(function() { echo "Handler 2\n"; }, ['once' => true]);

$wait = 2;
while ($wait--) {
    pcntl_alarm(1);
    sleep(1);
}
```

# Group Handler

The `ShoppingFeed\Signal\GroupHandler` object sits on the top of the signal handler, and allow to register and manage callbacks
against a list of signals.

```php
<?php
namespace ShoppingFeed\Signal;

$handler = new GroupHandler('optional_name');
$handler->append([SIGINT, SIGTERM, SIGQUIT], function(int $signal) {
    echo $signal . ' handled' . PHP_EOL;
});

// Simulates signals manually
$handler->handle(SIGINT); 
$handler->handle(SIGTERM); 
$handler->handle(SIGQUIT);

// Remove handlers for the following signals... 
$handler->clear([SIGINT, SIGTERM]); 

// ... Or all of them
$handler->clear();
```

# Global Handler

The global handler is an object that allow to manage all signals and groups from a single place.
If your application make uses of container oriented approach, the `ShoppingFeed\Signal\GlobalHandler` is a
good candidate for a shared service as all signals and groups states are hold here.

 
```php
<?php
namespace ShoppingFeed\Signal;

// Create the handler with default mode
$handler = new GlobalHandler(SignalHandlerInterface::PREV_ERROR);

// Group access
$handler->group('logger')
    ->append(SIGTERM, function() {
    // ...
    });

// Signals access
$handler->signal(SIGINT)
    ->append(function() {
    // ...
    });

// clear all signals for group(s) 
$handler->clearGroup(['logger']);

// clear all signals even within groups: 
$handler->clearSignal([SIGTERM]);

// clear everything
$handler->clear();
```

# Services

The instance of `ShoppingFeed\Signal\GlobalHandler` is provided as service thought dependencies configuration.
Within apps, you can access it as following

```php
<?php

$global = $container->get(\ShoppingFeed\Signal\GlobalHandler::class);
$global->signal(SIGINT)->append(function() {});
``` 

# Missing pcntl extension

When directly creating  `ShoppingFeed\Signal\SignalHandler` in environmment you're not sure that the `ext-pcntl` is available,
consider using the `factory` method instead of direct instantiation : it will check for the extension availability, and provide a fallback object
that will not break your application when underlying calls to the extension's functions are performed.

Note that if you manage signal from Global or Group handlers, you don't have to worry about it

```php
<?php
namespace ShoppingFeed\Signal;

// Return an instance of SignalHandlerFallback if the extension is not found
$handler = SignalHandler::factory(SIGTERM);

// cause a fatal error as the pcntl_* function are not present
$handler = new SignalHandler(SIGTERM);
```


