# HookPHP

[![Build Status](https://img.shields.io/travis/sciactive/hookphp.svg)](https://travis-ci.org/sciactive/hookphp) [![Latest Stable Version](https://img.shields.io/packagist/v/sciactive/hookphp.svg?style=flat)](https://packagist.org/packages/sciactive/hookphp) [![License](https://img.shields.io/packagist/l/sciactive/hookphp.svg?style=flat)](https://packagist.org/packages/sciactive/hookphp) [![Open Issues](https://img.shields.io/github/issues/sciactive/hookphp.svg?style=flat)](https://github.com/sciactive/hookphp/issues) [![Code Quality](https://img.shields.io/scrutinizer/g/sciactive/hookphp.svg?style=flat)](https://scrutinizer-ci.com/g/sciactive/hookphp/) [![Coverage](https://img.shields.io/scrutinizer/coverage/g/sciactive/hookphp.svg?style=flat)](https://scrutinizer-ci.com/g/sciactive/hookphp/)

Method hooking (decorators) in PHP.

## Installation

You can install HookPHP with Composer.

```sh
composer require sciactive/hookphp
```

## Getting Started

If you don't use an autoloader, all you need to do is include the Hook.php file.

```php
require("Hook.php");
```

Now you can start setting up objects for method hooking.

```php
class Test {
    function testFunction($string) {
        echo $string;
    }
}
$obj = new Test();
\SciActive\Hook::hookObject($obj, 'Test->');
```

And modifying their method calls.

```php
\SciActive\Hook::addCallback('Test->testFunction', -2, function(&$arguments, $name, &$object, &$function, &$data){
    $arguments[0] = 'New argument instead.';
});
```

And now calling a hooked method like this:

```php
$obj->testFunction("This won't print.");
```

Will output this:

```
New argument instead.
```

## Adding a Callback with addCallback

A callback is called either before a method runs or after. The callback is passed an array of arguments or return value which it can freely manipulate. If the callback runs before the method and sets the arguments array to false (or causes an error), the method will not be run. Callbacks before a method are passed the arguments given when the method was called, while callbacks after a method are passed the return value (in an array) of that method.

The callback can receive up to 5 arguments, in this order:

- `&$arguments` - An array of either arguments or a return value.
- `$name` - The name of the hook.
- `&$object` - The object on which the hook caught a method call.
- `&$function` - A callback for the method call which was caught. Altering this will cause a different function/method to run.
- `&$data` - An array in which callbacks can store data to communicate with later callbacks.

A hook is the name of whatever method it should catch. A hook can also have an arbitrary name, but be wary that it may already exist and it may result in your callback being falsely called. In order to reduce the chance of this, always use namespaced names to begin arbitrary hook names. E.g. '\\MyProject\\PlayerBonus'.

If the hook is called explicitly, callbacks defined to run before the hook will run immediately followed by callbacks defined to run after.

A negative $order value means the callback will be run before the method, while a positive value means it will be run after. The smaller the order number, the sooner the callback will be run. You can think of the order value as a timeline of callbacks, zero (0) being the actual method being hooked.

Additional identical callbacks can be added in order to have a callback called multiple times for one hook.

The hook "all" is a pseudo hook which will run regardless of what was actually caught. Callbacks attached to the "all" hook will run before callbacks attached to the actual hook.

Note: Be careful to avoid recursive callbacks, as they may result in an infinite loop.

The parameters and return value for `addCallback` are:

- @param string $hook The name of the hook to catch.
- @param int $order The order can be negative, which will run before the method, or positive, which will run after the method. It cannot be zero.
- @param callback The callback.
- @return array An array containing the IDs of the new callback and all matching callbacks.

## Options for a Callback

A callback is passed the following parameters:

- `&$arguments` - An array of either arguments or a return value.
- `$name` - The name of the hook.
- `&$object` - The object on which the hook caught a method call.
- `&$function` - A callback for the method call which was caught. Altering this will cause a different function/method to run.
- `&$data` - An array in which callbacks can store data to communicate with later callbacks.

A callback can alter `$arguments` to alter what is given to or returned from the method call.

A callback can replace or alter `$object` to affect what the method is being called on.

A callback can replace `$function` to cause a different function or method to run instead.

A callback can add and alter data in the `$data` array to communicate with other callbacks. This is especially useful when communicating with callbacks that run on the other side of the method call.

## Retrieving a Hooked Object

If you need to retrieve an object that has been hooked, you can use the `_hookObject` method:

```php
$originalObject = $hookedObject->_hookObject();
```

## Contacting the Developer

There are several ways to contact HookPHP's developer with your questions, concerns, comments, bug reports, or feature requests.

- HookPHP is part of [SciActive on Twitter](http://twitter.com/SciActive).
- Bug reports, questions, and feature requests can be filed at the [issues page](https://github.com/sciactive/hookphp/issues).
- You can directly [email Hunter Perrin](mailto:hunter@sciactive.com), the creator of HookPHP.
