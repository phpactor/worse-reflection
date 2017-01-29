Worse Reflection
==================

[![Build Status](https://travis-ci.org/dantleech/worse-reflection.svg?branch=master)](https://travis-ci.org/dantleech/worse-reflection)

This library aims to provide a light class-based AST based "reflection" library.

## Worse than Better

It is highly influenced by [BetterReflection](https://github.com/Roave/BetterReflection), diffrerences are as follows:

- Does not aim to implement built-in PHP reflection API.
- Focuses on class reflection.
- Only fully parses the AST once per file.

It is being developed to provide support for the
[Phpactor](https://github.com/dantleech/phpactor) introspection and
refactoring tool. And so is driven for that use case.

If you want comprehsnsive reflection, use BetterReflection. If you want faster
class-based reflection, then you can use this one.
