Worse Reflection
==================

[![Build Status](https://travis-ci.org/dantleech/worse-reflection.svg?branch=master)](https://travis-ci.org/dantleech/worse-reflection)

This library aims to provide a light class-based AST based "reflection" library.

## Worse than Better

It is influenced by [BetterReflection](https://github.com/Roave/BetterReflection), diffrerences are as follows:

- Does not aim to implement built-in PHP reflection API.
- Focuses on class reflection.
- Uses the amazing [Tolerant Parser](https://github.com/Microsoft/tolerant-php-parser).
- Uses the PHPStorm stubs to provide reflections of internal classes.

It is being developed to provide support for the
[Phpactor](https://github.com/dantleech/phpactor) introspection and
refactoring tool. And is therefore driven by that use case.

If you want comprehsnsive reflection, use BetterReflection. If you want faster
class-based reflection with no support and frequent BC breaks, then you can
use this one.

## Usage

```php
$reflector = new Reflector(new StringSourceLocator(SourceCode::fromString('<?php ...')));
$class = $reflector->reflectClass('Foobar');
$class->methods()->get('foobar')->visiblity() == Visibility::public();
$class->properties()->get('barbar')->visiblity() == Visibility::public();

/** @var ReflectionMethod */
foreach ($class->methods() as $method) {
    echo $method->name();
}
```
