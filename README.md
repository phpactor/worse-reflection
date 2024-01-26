Worse Reflection
================

> This package is not actively developed here but in the main
> [phpactor](https://github.com/phpactor/phpactor) repository. It is
> _occasionally_ updated. Raise any issues about this repository there.

This library aims to provide a light class-based AST based "reflection" library.

## Worse than Better

It is influenced by [BetterReflection](https://github.com/Roave/BetterReflection), diffrerences are as follows:

- Can reflect variables.
- Does not aim to implement built-in PHP reflection API.
- Uses the amazing [Tolerant Parser](https://github.com/Microsoft/tolerant-php-parser).
- Uses the PHPStorm stubs to provide reflections of internal classes.

It is being developed to provide support for the
[Phpactor](https://github.com/dantleech/phpactor) introspection and
refactoring tool. And is therefore driven by that use case.

If you want comprehsnsive reflection, use BetterReflection. If you want faster
reflection including type/value flow with no support and frequent BC breaks, then you can
use this one (note that I havn't benchmarked BR in sometime, it may well be faster now).

## Usage

```php
$reflector = ReflectorBuilder::create()
    ->addSource('<?php ...')
    ->build();

$class = $reflector->reflectClass('Foobar');

$class->methods()->get('foobar')->visiblity()    == Visibility::public();
$class->properties()->get('barbar')->visiblity() == Visibility::public();

/** @var ReflectionMethod */
foreach ($class->methods() as $method) {
    echo $method->name();                                  // methodName
    echo $method->returnType()->short();                   // Foobar
    echo (string) $method->returnType();                   // This\Is\Foobar
    echo (string) $method->inferredReturnTypes()->best(); // from docblock if it exists

    foreach ($method->parameters() as $parameter) {
        $parameter->name();                      // paramName
        (string) $parameter->inferredType();     // Fully\Qualified\ParamType
    }

}

foreach ($class->traits() as $trait) {
    // ...
}

foreach ($class->interfaes() as $interface) {
    // ...
}

foreach ($class->method('foobar')->frame()->locals() as $variable) {
    $variable->offset()->toInt(); // byte offset
    $variable->type();            // variable type (if available )
    $variable->value();           // variable value (if available)
}

$offset = $reflection->reflectOffset(
    SourceCode::fromString('...'), 
    Offset::fromInt(1234)
);

$offset->value()->type();    // type at offset (if available)
$offset->value()->value();   // value (e.g. 1234)
$offset->frame();            // return frame
```

See tests for more examples...

Contributing
------------

This package is open source and welcomes contributions! Feel free to open a
pull request on this repository.

Support
-------

- Create an issue on the main [Phpactor](https://github.com/phpactor/phpactor) repository.
- Join the `#phpactor` channel on the Slack [Symfony Devs](https://symfony.com/slack-invite) channel.

