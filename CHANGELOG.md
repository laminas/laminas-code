# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.3.0 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.2.0 - 2017-07-23

### Added

- [zendframework/zend-code#112](https://github.com/zendframework/zend-code/pull/112)
  [zendframework/zend-code#110](https://github.com/zendframework/zend-code/pull/110) Introduced
  support for the PHP `7.2` `object` type-hint
- [zendframework/zend-code#41](https://github.com/zendframework/zend-code/pull/41) Added `VarTag`
  support to the docblock generators and reflectors: allows generating
  and parsing `@var` tags.
- [zendframework/zend-code#113](https://github.com/zendframework/zend-code/pull/113) Added
  PHP `7.2` to the build matrix
- [zendframework/zend-code#114](https://github.com/zendframework/zend-code/pull/114) Increased
  minimum supported PHP version to `^7.1.0`
- [zendframework/zend-code#114](https://github.com/zendframework/zend-code/pull/114) Upgraded
  PHPUnit dependency to `^6.2.2`
- [zendframework/zend-code#121](https://github.com/zendframework/zend-code/pull/121) Imported
  global functions via `use` statements (experimenting with OpCache
  OPCODE inlining optimisations)

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-code#113](https://github.com/zendframework/zend-code/pull/113)
  [zendframework/zend-code#118](https://github.com/zendframework/zend-code/pull/118) Removed
  HHVM support
- [zendframework/zend-code#122](https://github.com/zendframework/zend-code/pull/122) Removed
  IRC notifications for pushes/build statuses

### Fixed

- [zendframework/zend-code#101](https://github.com/zendframework/zend-code/pull/101) avoid
  calling `isInternalPhpType` twice in the `TypeGenerator`
- [zendframework/zend-code#115](https://github.com/zendframework/zend-code/pull/115) Replaced
  assertions in the test suite with their static counterparts where
  applicable
- [zendframework/zend-code#120](https://github.com/zendframework/zend-code/pull/120)
  [zendframework/zend-code#109](https://github.com/zendframework/zend-code/pull/109)
  [zendframework/zend-code#100](https://github.com/zendframework/zend-code/pull/100) Applied
  Laminas coding standard to the library code
- [zendframework/zend-code#119](https://github.com/zendframework/zend-code/pull/119) Corrected
  test suite errors caused by mismatching parameter order
- [zendframework/zend-code#106](https://github.com/zendframework/zend-code/pull/106)
- [zendframework/zend-code#107](https://github.com/zendframework/zend-code/pull/107) Minor
  typing error corrections in documentation and error messages

## 3.1.0 - 2016-10-24

### Added

- [zendframework/zend-code#87](https://github.com/zendframework/zend-code/pull/95) support for
  PHP 7.1's `void` return type declaration.
- [zendframework/zend-code#87](https://github.com/zendframework/zend-code/pull/95) support for
  PHP 7.1's nullable type declarations.
- [zendframework/zend-code#87](https://github.com/zendframework/zend-code/pull/95) support for
  PHP 7.1's `iterable` type declaration.
- [zendframework/zend-code#62](https://github.com/zendframework/zend-code/pull/62) added
  `Laminas\Code\Generator\MethodGenerator#getReturnType()` accessor.
- [zendframework/zend-code#68](https://github.com/zendframework/zend-code/pull/68)
  [zendframework/zend-code#26](https://github.com/zendframework/zend-code/pull/26) added mutators
  to allow removing/checking for existence of methods, properties, constants,
  parameters and type declarations across all the code generator API.
- [zendframework/zend-code#65](https://github.com/zendframework/zend-code/pull/65) continuous
  integration testing now checks locked, newest and oldest dependency
  sets.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.5 - 2016-10-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#92](https://github.com/zendframework/zend-code/pull/92) corrected
  `Laminas\Code\Scanner\ClassScanner` to detect multiple interface inheritance.
- [zendframework/zend-code#95](https://github.com/zendframework/zend-code/pull/95) corrected
  `Laminas\Code\Generator\ParameterGenerator` to allow copying parameter signatures
  for non-optional parameters that are still nullable via a default `= null`
  value.
- [zendframework/zend-code#94](https://github.com/zendframework/zend-code/pull/94) corrected
  `Laminas\Code\Generator\ValueGenerator` so that class constants can now
  be generated with arrays as default value (supported since PHP 5.6).

## 3.0.4 - 2016-06-30

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#59](https://github.com/zendframework/zend-code/pull/59) fixes an issue with
  detection of multiple trait `use` statements.
- [zendframework/zend-code#75](https://github.com/zendframework/zend-code/pull/75) provides a patch to
  ensure that `extends` statements qualify the parent class based on the current
  namespace and/or import statements.

## 3.0.3 - 2016-06-27

### Added

- [zendframework/zend-code#66](https://github.com/zendframework/zend-code/pull/66) publishes the
  documentation to https://docs.laminas.dev/laminas-code/.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#61](https://github.com/zendframework/zend-code/pull/61) fixes an issue with
  how parameter typehints were generated; previously, fully-qualified class
  names were not being generated with the leading backslash, causing them to
  attempt to resolve as if they were relative to the current namespace.
- [zendframework/zend-code#69](https://github.com/zendframework/zend-code/pull/69) fixes an issue with
  how class names under the same namespace are generated when generating
  typehints, extends, and implements values; they now strip the
  common namespace from the class name.
- [zendframework/zend-code#72](https://github.com/zendframework/zend-code/pull/72) fixes an issue
  within the `TokenArrayScanner` when scanning closures.

## 3.0.2 - 2016-04-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#52](https://github.com/zendframework/zend-code/pull/52) updates several
  dependency constraints:
  - laminas-stdlib now allows either the 2.7 or 3.0 series, as the APIs consumed by
    laminas-code are compatible across versions.
  - PHP now excludes the 7.0.5 release, as it has known issues in its tokenizer
    implementation that make the laminas-code token scanner unusable.
- [zendframework/zend-code#46](https://github.com/zendframework/zend-code/pull/46) updates all
  generators to use `\n` for line endings in generated code, vs `PHP_EOL`,
  ensuring cross-platform consistency.

## 3.0.1 - 2016-01-26

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#34](https://github.com/zendframework/zend-code/pull/34) method name cannot be optional when adding a method
  to a class generator.
- [zendframework/zend-code#38](https://github.com/zendframework/zend-code/pull/38) PHP_CodeSniffer was moved to dev dependencies

## 3.0.0 - 2016-01-13

### Changed

This section refers to breaking changes: please refer to
[doc/book/migration.md](doc/book/migration.md) for migration instructions.

- Types `string`, `int`, `float`, `bool` passed to `Laminas\Code\Generator\ParameterGenerator#setType()`
  are no longer ignored in generated code [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Types declared in DocBlocks are now ignored when creating a `Laminas\Code\Generator\ParameterGenerator` via
  `Laminas\Code\Generator\ParameterGenerator::fromReflection()`. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Type strings are now validated: passing an invalid type to any method in the generator API
  may lead to a `Laminas\Code\Generator\InvalidArgumentException` being thrown.
  [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator\ParameterGenerator::$simple` was removed. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator\ParameterGenerator#$type` is now a `null|Laminas\Code\Generator\TypeGenerator`: was a
  `string` before. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator` type-hints are now always prefixed with the namespace separator `\`.
  [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Reflection\ParameterReflection#getType()` was renamed 
  to `Laminas\Code\Reflection\ParameterReflection#detectType()` in order to not override the inherited
  `ReflectionParameter#getType()`, introduced in PHP 7. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

### Added

- PHP 7 return type hints generation support via `Laminas\Code\Generator\MethodGenerator#setReturnType()`.
  [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- PHP 7 scalar type hints generation support via `Laminas\Code\Generator\ParameterGenerator#setType()` and 
  `Laminas\Code\Generator\ParameterGenerator#getType()`. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- PHP 5.6 variadic arguments support via `Laminas\Code\Generator\ParameterGenerator#setVariadic()` and
  `Laminas\Code\Generator\ParameterGenerator#getVariadic()`. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Generation of methods returning by reference is supported via `Laminas\Code\Generator\ParameterGenerator#setReturnsReference()`.
  [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

### Deprecated

- Nothing.

### Removed

- `Laminas\Code\ParameterGenerator::$simple` was removed. [zendframework/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

### Fixed

- Nothing.

## 2.6.2 - 2015-01-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#31](https://github.com/zendframework/zend-code/pull/31) updated license year.

## 2.6.2 - 2015-01-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#31](https://github.com/zendframework/zend-code/pull/31) updated license year.

## 2.6.1 - 2015-11-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#25](https://github.com/zendframework/zend-code/pull/25) changes the
  `doctrine/common` suggestion/dev-dependency to the more specific
  `doctrine/annotations` package (which is what is actually consumed).

## 2.6.0 - 2015-11-18

### Added

- [zendframework/zend-code#12](https://github.com/zendframework/zend-code/pull/12) adds the ability to
  generate arrays using either long/standard syntax (`array(...)`) or short
  syntax (`[...]`). This can be accomplished by setting the value type to
  `ValueGenerator::TYPE_ARRAY_SHORT` instead of using `TYPE_ARRAY`.
  Additionally, you can use `TYPE_ARRAY_LONG` instead of `TYPE_ARRAY`; the two
  constants are synonyms.
- [zendframework/zend-code#11](https://github.com/zendframework/zend-code/pull/11) adds the ability to
  generate interfaces via the new class `Laminas\Code\Generator\InterfaceGenerator`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#20](https://github.com/zendframework/zend-code/pull/20) updates
  the laminas-eventmanager dependency to `^2.6|^3.0`, and changes its
  internal usage to use the `triggerEventUntil()` signature.

## 2.5.3 - 2015-11-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-code#10](https://github.com/zendframework/zend-code/pull/10) removes a
  development dependency on zendframework/zend-version.
- [zendframework/zend-code#23](https://github.com/zendframework/zend-code/pull/23) removes a
  requirement on laminas/laminas-stdlib. This results in a slight change in
  `Laminas\Code\Generator\ValueGenerator`: `setConstants()` and `getConstants()`
  can now receive/emit *either* an SPL `ArrayObject` or
  `Laminas\Stdlib\ArrayObject`. Since these are functionally equivalent, however,
  you will experience no change in behavior.

### Fixed

- Nothing.
