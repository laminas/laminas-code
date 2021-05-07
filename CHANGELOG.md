# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 4.2.2 - 2021-05-07


-----

### Release Notes for [4.2.2](https://github.com/laminas/laminas-code/milestone/14)

4.2.x bugfix release (patch)

### 4.2.2

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [81: Fix `DocBlockGenerator` formatting issue when docblock long description is not available](https://github.com/laminas/laminas-code/pull/81) thanks to @martin-helmich

## 4.2.1 - 2021-04-23


-----

### Release Notes for [4.2.1](https://github.com/laminas/laminas-code/milestone/12)

4.2.x bugfix release (patch)

### 4.2.1

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [77: Consider missing indices in PropertyGenerator](https://github.com/laminas/laminas-code/pull/77) thanks to @rieschl

## 4.2.0 - 2021-04-14


-----

### Release Notes for [4.2.0](https://github.com/laminas/laminas-code/milestone/11)

Feature release (minor)

### 4.2.0

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### Enhancement

 - [72: Make sure variadic parameter does not have default value](https://github.com/laminas/laminas-code/pull/72) thanks to @mchekin

#### Bug,Enhancement,Help Wanted

 - [35: `ParameterGenerator` always fails with variadic parameters](https://github.com/laminas/laminas-code/issues/35) thanks to @michalbundyra

## 4.1.0 - 2021-03-27


-----

### Release Notes for [4.1.0](https://github.com/laminas/laminas-code/milestone/8)

Feature release (minor)

### 4.1.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement

 - [70: Improve `TraitGenerator` test code coverage](https://github.com/laminas/laminas-code/pull/70) thanks to @mchekin
 - [68: &#91;Feature&#93; Add/update type information for source files](https://github.com/laminas/laminas-code/pull/68) thanks to @lisachenko

#### Documentation

 - [65: Correction of Laminas\Code\Generator\DocBlock\Tag\ParamTag example](https://github.com/laminas/laminas-code/pull/65) thanks to @mdthh

## 4.0.0 - 2020-12-30

### Added

- [laminas/laminas-code#57](https://github.com/laminas/laminas-code/pull/57) support for following PHP 8
  types have been added:
   * union types
   * `false`
   * `mixed`
   * `null`
   * `static`

### Changed

- [laminas/laminas-code#57](https://github.com/laminas/laminas-code/pull/57) due to internal refactoring
  requiring better internal types, the minimum supported PHP version is now `7.4`
- BC BREAK [laminas/laminas-code#38](https://github.com/laminas/laminas-code/pull/38) changed generated class
  output to no longer contain excessive whitespace around contents. The structure of the generated output
  will still be the same, but spacing changed, which will likely lead o breakages if you
  are asserting/depending upon the strings produced by `Laminas\Code\Generator\ClassGenerator#generate()`
- BC BREAK [laminas/laminas-code#42](https://github.com/laminas/laminas-code/pull/42) `declare()` statements
  will now be generated above `namespace` declarations: this is to be better compliant with PSR-2, but it
  may break downstream applications if these rely on the stability of code produced
  by `Laminas\Code\Generator\FileGenerator#generate()`

### Removed

- BC BREAK [zendframwork/zend-code#153](https://github.com/zendframework/zend-code/pull/153) Removed
  the annotation API from the library
- BC BREAK [laminas/laminas-code#39](https://github.com/laminas/laminas-code/pull/39) the
  `laminas/laminas-zendframework-bridge` has been removed, and is instead to be installed optionally
  by consumers of the library. If you are still migrating from `zendframework/zend-code`, you will
  need to add it to your `composer.json` yourself, or directly use to `Laminas\Code` symbols.
- BC BREAK [laminas/laminas-code#58](https://github.com/laminas/laminas-code/pull/58) the `Scanner`
  and `Annotation` components have been removed, and with it all API that interacted with it:
   * `Laminas\Code\Generator\FileGeneratorRegistry` has been deleted
   * `Laminas\Code\Generator\FileGenerator::fromReflectedFileName()` was removed
   * `Laminas\Code\Generator\FileGenerator::fromReflection()` was removed
   * `Laminas\Code\NameInformation` has been deleted
   * `Laminas\Code\Reflection\ReflectionInterface` was marked `@internal`
   * `Laminas\Code\Reflection\FileReflection` has been deleted
   * `Laminas\Code\Generic\Prototype\PrototypeInterface` was marked `@internal`
   * `Laminas\Code\Generic\Prototype\PrototypeClassFactory` was marked `@internal`
   * `Laminas\Code\Generic\Prototype\PrototypeGenericInterface` was marked `@internal`
   * `Laminas\Code\Annotation\AnnotationInterface` has been deleted
   * `Laminas\Code\Annotation\Parser\GenericAnnotationParser` has been deleted
   * `Laminas\Code\Annotation\Parser\ParserInterface` has been deleted
   * `Laminas\Code\Annotation\Parser\DoctrineAnnotationParser` has been deleted
   * `Laminas\Code\Annotation\AnnotationManager` has been deleted
   * `Laminas\Code\Annotation\AnnotationCollection` has been deleted
   * `Laminas\Code\Scanner\ValueScanner` has been deleted
   * `Laminas\Code\Scanner\DirectoryScanner` has been deleted
   * `Laminas\Code\Scanner\DocBlockScanner#$nameInformation` was removed
   * `Laminas\Code\Scanner\DocBlockScanner#$annotationManager` was removed
   * `Laminas\Code\Scanner\DocBlockScanner#$annotations` was removed
   * `Laminas\Code\Scanner\DocBlockScanner#getAnnotations()` was removed
   * `Laminas\Code\Scanner\DocBlockScanner` was marked `@internal`
   * `Laminas\Code\Scanner\ConstantScanner` has been deleted
   * `Laminas\Code\Scanner\FunctionScanner` has been deleted
   * `Laminas\Code\Scanner\AnnotationScanner` has been deleted
   * `Laminas\Code\Scanner\DerivedClassScanner` has been deleted
   * `Laminas\Code\Scanner\ClassScanner` has been deleted
   * `Laminas\Code\Scanner\AggregateDirectoryScanner` has been deleted
   * `Laminas\Code\Scanner\TokenArrayScanner` has been deleted
   * `Laminas\Code\Scanner\ParameterScanner` has been deleted
   * `Laminas\Code\Scanner\FileScanner` has been deleted
   * `Laminas\Code\Scanner\PropertyScanner` has been deleted
   * `Laminas\Code\Scanner\CachingFileScanner` has been deleted
   * `Laminas\Code\Scanner\ScannerInterface` has been deleted
   * `Laminas\Code\Scanner\MethodScanner` has been deleted
   * `Laminas\Code\Scanner\Util` has been deleted


-----

### Release Notes for [4.0.0](https://github.com/laminas/laminas-code/milestone/1)

next backward compatibility break release (major)

### 4.0.0

- Total issues resolved: **21**
- Total pull requests resolved: **13**
- Total contributors: **9**

#### Enhancement

 - [64: Verify `4.0.x` branch against real-world applications before releasing](https://github.com/laminas/laminas-code/issues/64) thanks to @Ocramius
 - [63: Feature - #62 upgrade coding standard and lock dependencies](https://github.com/laminas/laminas-code/pull/63) thanks to @Ocramius
 - [61: fix #45 : Psalm integration](https://github.com/laminas/laminas-code/pull/61) thanks to @fezfez and @boesing
 - [60: Remove travi-ci and move to github action](https://github.com/laminas/laminas-code/pull/60) thanks to @fezfez and @weierophinney
 - [59: Merge release 3.5.1 into 4.0.x](https://github.com/laminas/laminas-code/pull/59) thanks to @github-actions[bot]
 - [57: #53 support php-8.0 type system additions](https://github.com/laminas/laminas-code/pull/57) thanks to @Ocramius and @weierophinney
 - [56: #48 merge release `3.5.0` into development branch for `4.0.x`](https://github.com/laminas/laminas-code/pull/56) thanks to @Ocramius
 - [10: Additional blank lines make generated class not PSR2 compliant](https://github.com/laminas/laminas-code/issues/10) thanks to @weierophinney

#### BC Break,Enhancement

 - [58: BC BREAK: removed `Scanner` component, removed `FileReflection`, removed `NameInformation` and made `DocBlockScanner` `@internal`](https://github.com/laminas/laminas-code/pull/58) thanks to @Ocramius
 - [39: Suggest &quot;laminas/laminas-zendframework-bridge&quot;](https://github.com/laminas/laminas-code/pull/39) thanks to @olvlvl
 - [12: Remove code scanners to improve library maintainability](https://github.com/laminas/laminas-code/issues/12) thanks to @weierophinney

#### Duplicate,Enhancement

 - [54: #53 added support for generating parameters with union types in their definitions](https://github.com/laminas/laminas-code/pull/54) thanks to @fezfez and @weierophinney
 - [48: Merge release 3.5.0 into 4.0.x](https://github.com/laminas/laminas-code/pull/48) thanks to @github-actions[bot]

#### Bug

 - [50: Some constants are not rendered properly depending on environment values](https://github.com/laminas/laminas-code/pull/50) thanks to @drupol

#### Bug,Unit Test Needed

 - [42: Moved declare statements above namespace](https://github.com/laminas/laminas-code/pull/42) thanks to @jguittard

#### Awaiting Author Updates,BC Break

 - [38: Generating class in a better format.](https://github.com/laminas/laminas-code/pull/38) thanks to @tiagosampaio

#### Bug,Duplicate,Won't Fix

 - [28: DirectoryScanner-&gt;getClasses() returns no classes](https://github.com/laminas/laminas-code/issues/28) thanks to @weierophinney

#### Bug,Won't Fix

 - [27: Zend\Code\Scanner\MethodScanner does not account for scalar type declarations](https://github.com/laminas/laminas-code/issues/27) thanks to @weierophinney
 - [26: v3 only: Unknown Notices from ClassScanner](https://github.com/laminas/laminas-code/issues/26) thanks to @weierophinney
 - [22: Update AnnotationScanner.php](https://github.com/laminas/laminas-code/issues/22) thanks to @weierophinney
 - [18: Notice when using array as default value for parameter](https://github.com/laminas/laminas-code/issues/18) thanks to @weierophinney
 - [15: E&#95;NOTICE with trait method aliases](https://github.com/laminas/laminas-code/issues/15) thanks to @weierophinney
 - [9: Zend\Code\Reflection\FileReflection getBody()](https://github.com/laminas/laminas-code/issues/9) thanks to @weierophinney
 - [8: Zend\Code\Reflection\FileReflection crashes on Zend's module.config.php](https://github.com/laminas/laminas-code/issues/8) thanks to @weierophinney
 - [3: Not using return value of array&#95;map in ClassGenerator-&gt;setImplementedInterfaces](https://github.com/laminas/laminas-code/issues/3) thanks to @weierophinney

#### Question,Won't Fix

 - [25: Use Reflection\FileReflection without including file? ](https://github.com/laminas/laminas-code/issues/25) thanks to @weierophinney

#### Enhancement,Won't Fix

 - [19: Support nullable types](https://github.com/laminas/laminas-code/issues/19) thanks to @weierophinney
 - [1: perfect FunctionScanner class ](https://github.com/laminas/laminas-code/issues/1) thanks to @weierophinney

#### BC Break,Enhancement,Question

 - [17: Deprecate scanner functionality (4.0), drop it (5.0) in favor of ](https://github.com/laminas/laminas-code/issues/17) thanks to @weierophinney

## 3.5.1 - 2020-11-30

### Added

### Release Notes for [3.5.1](https://github.com/laminas/laminas-code/milestone/4)

3.5.x bugfix release (patch)

### 3.5.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Enhancement

 - [55: Use `8.0` instead of `nightly` in travis-ci builds](https://github.com/laminas/laminas-code/pull/55) thanks to @fezfez

## 3.5.0 - 2020-11-11

### Added

- [#47](https://github.com/laminas/laminas-code/pull/47) adds support for PHP 8. NOTE: this simply means the code runs on PHP 8, not that it can generate code specific to PHP 8.


-----

### Release Notes for [3.5.0](https://github.com/laminas/laminas-code/milestone/3)

next feature release (minor)

### 3.5.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement,hacktoberfest-accepted

 - [47: Start PHP 8.0 support](https://github.com/laminas/laminas-code/pull/47) thanks to @Slamdunk

 - [43: QA fix for phpstan](https://github.com/laminas/laminas-code/pull/43) thanks to @janvernieuwe

#### Documentation

 - [31: Modify the examples to make them compatible with PSR-12](https://github.com/laminas/laminas-code/pull/31) thanks to @simivar

## 3.4.1 - 2019-12-10

### Added

- [zendframwork/zend-code#180](https://github.com/zendframework/zend-code/pull/180) adds support for PHP 7.4.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#179](https://github.com/zendframework/zend-code/pull/179) fixes exception message when invalid value provided in `Laminas\Code\Generator\ValueGenerator`.

- [zendframwork/zend-code#180](https://github.com/zendframework/zend-code/pull/180) fixes PHP 7.4 compatibility.

## 3.4.0 - 2019-10-06

### Added

- [zendframwork/zend-code#170](https://github.com/zendframework/zend-code/pull/170) adds class constant visibility modifiers support.

- [zendframwork/zend-code#169](https://github.com/zendframework/zend-code/pull/169) adds the ability to define declare statements.

- [zendframwork/zend-code#167](https://github.com/zendframework/zend-code/pull/167) adds the ability to remove doc block of a member.

### Changed

- [zendframwork/zend-code#166](https://github.com/zendframework/zend-code/pull/166) changes omitting default property value if it is null.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#172](https://github.com/zendframework/zend-code/pull/172) fixes PHP 7.4 compatibility.

## 3.3.2 - 2019-08-31

### Added

- [zendframwork/zend-code#162](https://github.com/zendframework/zend-code/pull/162) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#171](https://github.com/zendframework/zend-code/pull/171) changes
  curly braces in array and string offset access to square brackets
  in order to prevent issues under the upcoming PHP 7.4 release.

- [zendframwork/zend-code#164](https://github.com/zendframework/zend-code/pull/164) fixes indentation in multi-level arrays generated by `ValueGenerator`.

## 3.3.1 - 2018-08-13

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#158](https://github.com/zendframework/zend-code/pull/158) updates several `switch` cases to use `break` instead of `continue`
  in order to prevent issues under the upcoming PHP 7.3 release.

- [zendframwork/zend-code#147](https://github.com/zendframework/zend-code/pull/147) fixes the regular expression used for `@var` annotations to
  allow omission of the variable name.

- [zendframwork/zend-code#146](https://github.com/zendframework/zend-code/pull/146) updates all `@return` annotations to reflect the correct types returned by each method.

- [zendframwork/zend-code#144](https://github.com/zendframework/zend-code/pull/144) fixes the class generator such that it now resolves
  `setExtendedClass()` arguments to aliases provided to the generator.

- [zendframwork/zend-code#140](https://github.com/zendframework/zend-code/pull/140) fixes `MethodScanner::setVisibility()` such that it no longer
  casts the provided visibility token to lower case; this fix is necessary, as
  the method is supposed to expect only the appropriate
  `T_(PUBLIC|PROTECTED|PRIVATE)` token values, which are integers.

- [zendframwork/zend-code#140](https://github.com/zendframework/zend-code/pull/140) updates the `MethodScanner::setVisibility()` method to raise
  a package-specific `InvalidArgumentException` instead of the non-existent
  package `Exception` class when an invalid visibility is provided.

## 3.3.0 - 2017-10-20

### Added

- [zendframwork/zend-code#131](https://github.com/zendframework/zend-code/pull/131) added the
  ability to omit a parameter type declaration
- [zendframwork/zend-code#132](https://github.com/zendframework/zend-code/pull/132) added a
  lightweight `MethodGenerator::copyMethodSignature()` constructor that
  only copies the method declaration with no body nor docblock.
- [zendframwork/zend-code#134](https://github.com/zendframework/zend-code/pull/134) short array
  notation is now used by default for generated array values
- [zendframwork/zend-code#136](https://github.com/zendframework/zend-code/pull/136) added the
  ability to specify an `omitdefaultvalue` key when using
  `ParameterGenerator::fromArray()`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#130](https://github.com/zendframework/zend-code/pull/130) Updated 
  links to the documentation
- [zendframwork/zend-code#133](https://github.com/zendframework/zend-code/pull/133) The default
  value of a `ParameterGenerator` is always a `ValueGenerator`

## 3.2.0 - 2017-07-23

### Added

- [zendframework/zend-code#112](https://github.com/zendframework/zend-code/pull/112)
  [zendframework/zend-code#110](https://github.com/zendframework/zend-code/pull/110) Introduced
  support for the PHP `7.2` `object` type-hint
- [zendframwork/zend-code#41](https://github.com/zendframework/zend-code/pull/41) Added `VarTag`
  support to the docblock generators and reflectors: allows generating
  and parsing `@var` tags.
- [zendframwork/zend-code#113](https://github.com/zendframework/zend-code/pull/113) Added
  PHP `7.2` to the build matrix
- [zendframwork/zend-code#114](https://github.com/zendframework/zend-code/pull/114) Increased
  minimum supported PHP version to `^7.1.0`
- [zendframwork/zend-code#114](https://github.com/zendframework/zend-code/pull/114) Upgraded
  PHPUnit dependency to `^6.2.2`
- [zendframwork/zend-code#121](https://github.com/zendframework/zend-code/pull/121) Imported
  global functions via `use` statements (experimenting with OpCache
  OPCODE inlining optimisations)

### Deprecated

- Nothing.

### Removed

- [zendframwork/zend-code#113](https://github.com/zendframework/zend-code/pull/113)
  [zendframwork/zend-code#118](https://github.com/zendframework/zend-code/pull/118) Removed
  HHVM support
- [zendframwork/zend-code#122](https://github.com/zendframework/zend-code/pull/122) Removed
  IRC notifications for pushes/build statuses

### Fixed

- [zendframwork/zend-code#101](https://github.com/zendframework/zend-code/pull/101) avoid
  calling `isInternalPhpType` twice in the `TypeGenerator`
- [zendframwork/zend-code#115](https://github.com/zendframework/zend-code/pull/115) Replaced
  assertions in the test suite with their static counterparts where
  applicable
- [zendframwork/zend-code#120](https://github.com/zendframework/zend-code/pull/120)
  [zendframwork/zend-code#109](https://github.com/zendframework/zend-code/pull/109)
  [zendframwork/zend-code#100](https://github.com/zendframework/zend-code/pull/100) Applied
  Laminas coding standard to the library code
- [zendframwork/zend-code#119](https://github.com/zendframework/zend-code/pull/119) Corrected
  test suite errors caused by mismatching parameter order
- [zendframwork/zend-code#106](https://github.com/zendframework/zend-code/pull/106)
- [zendframwork/zend-code#107](https://github.com/zendframework/zend-code/pull/107) Minor
  typing error corrections in documentation and error messages

## 3.1.0 - 2016-10-24

### Added

- [zendframwork/zend-code#87](https://github.com/zendframework/zend-code/pull/87) support for
  PHP 7.1's `void` return type declaration.
- [zendframwork/zend-code#87](https://github.com/zendframework/zend-code/pull/87) support for
  PHP 7.1's nullable type declarations.
- [zendframwork/zend-code#87](https://github.com/zendframework/zend-code/pull/87) support for
  PHP 7.1's `iterable` type declaration.
- [zendframwork/zend-code#62](https://github.com/zendframework/zend-code/pull/62) added
  `Laminas\Code\Generator\MethodGenerator#getReturnType()` accessor.
- [zendframwork/zend-code#68](https://github.com/zendframework/zend-code/pull/68)
  [zendframwork/zend-code#26](https://github.com/zendframework/zend-code/pull/26) added mutators
  to allow removing/checking for existence of methods, properties, constants,
  parameters and type declarations across all the code generator API.
- [zendframwork/zend-code#65](https://github.com/zendframework/zend-code/pull/65) continuous
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

- [zendframwork/zend-code#92](https://github.com/zendframework/zend-code/pull/92) corrected
  `Laminas\Code\Scanner\ClassScanner` to detect multiple interface inheritance.
- [zendframwork/zend-code#95](https://github.com/zendframework/zend-code/pull/95) corrected
  `Laminas\Code\Generator\ParameterGenerator` to allow copying parameter signatures
  for non-optional parameters that are still nullable via a default `= null`
  value.
- [zendframwork/zend-code#94](https://github.com/zendframework/zend-code/pull/94) corrected
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

- [zendframwork/zend-code#59](https://github.com/zendframework/zend-code/pull/59) fixes an issue with
  detection of multiple trait `use` statements.
- [zendframwork/zend-code#75](https://github.com/zendframework/zend-code/pull/75) provides a patch to
  ensure that `extends` statements qualify the parent class based on the current
  namespace and/or import statements.

## 3.0.3 - 2016-06-27

### Added

- [zendframwork/zend-code#66](https://github.com/zendframework/zend-code/pull/66) publishes the
  documentation to https://docs.laminas.dev/laminas-code/.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#61](https://github.com/zendframework/zend-code/pull/61) fixes an issue with
  how parameter typehints were generated; previously, fully-qualified class
  names were not being generated with the leading backslash, causing them to
  attempt to resolve as if they were relative to the current namespace.
- [zendframwork/zend-code#69](https://github.com/zendframework/zend-code/pull/69) fixes an issue with
  how class names under the same namespace are generated when generating
  typehints, extends, and implements values; they now strip the
  common namespace from the class name.
- [zendframwork/zend-code#72](https://github.com/zendframework/zend-code/pull/72) fixes an issue
  within the `TokenArrayScanner` when scanning closures.

## 3.0.2 - 2016-04-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#52](https://github.com/zendframework/zend-code/pull/52) updates several
  dependency constraints:
  - laminas-stdlib now allows either the 2.7 or 3.0 series, as the APIs consumed by
    laminas-code are compatible across versions.
  - PHP now excludes the 7.0.5 release, as it has known issues in its tokenizer
    implementation that make the laminas-code token scanner unusable.
- [zendframwork/zend-code#46](https://github.com/zendframework/zend-code/pull/46) updates all
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

- [zendframwork/zend-code#34](https://github.com/zendframework/zend-code/pull/34) method name cannot be optional when adding a method
  to a class generator.
- [zendframwork/zend-code#38](https://github.com/zendframework/zend-code/pull/38) PHP_CodeSniffer was moved to dev dependencies

## 3.0.0 - 2016-01-13

### Changed

- [zendframwork/zend-code#140](https://github.com/zendframework/zend-code/pull/140) updates the `MethodScanner::setVisibility()` method to raise a package-specific `InvalidArgumentException` instead of
  the non-existent package `Exception` class when an invalid visibility is provided.

This section refers to breaking changes: please refer to
[docs/book/migration.md](docs/book/migration.md) for migration instructions.

- Types `string`, `int`, `float`, `bool` passed to `Laminas\Code\Generator\ParameterGenerator#setType()`
  are no longer ignored in generated code [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Types declared in DocBlocks are now ignored when creating a `Laminas\Code\Generator\ParameterGenerator` via
  `Laminas\Code\Generator\ParameterGenerator::fromReflection()`. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Type strings are now validated: passing an invalid type to any method in the generator API
  may lead to a `Laminas\Code\Generator\InvalidArgumentException` being thrown.
  [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator\ParameterGenerator::$simple` was removed. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator\ParameterGenerator#$type` is now a `null|Laminas\Code\Generator\TypeGenerator`: was a
  `string` before. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Generator` type-hints are now always prefixed with the namespace separator `\`.
  [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- `Laminas\Code\Reflection\ParameterReflection#getType()` was renamed 
  to `Laminas\Code\Reflection\ParameterReflection#detectType()` in order to not override the inherited
  `ReflectionParameter#getType()`, introduced in PHP 7. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

### Added

- PHP 7 return type hints generation support via `Laminas\Code\Generator\MethodGenerator#setReturnType()`.
  [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- PHP 7 scalar type hints generation support via `Laminas\Code\Generator\ParameterGenerator#setType()` and 
  `Laminas\Code\Generator\ParameterGenerator#getType()`. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- PHP 5.6 variadic arguments support via `Laminas\Code\Generator\ParameterGenerator#setVariadic()` and
  `Laminas\Code\Generator\ParameterGenerator#getVariadic()`. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)
- Generation of methods returning by reference is supported via `Laminas\Code\Generator\ParameterGenerator#setReturnsReference()`.
  [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

### Deprecated

- Nothing.

### Removed

- `Laminas\Code\ParameterGenerator::$simple` was removed. [zendframwork/zend-code#30](https://github.com/zendframework/zend-code/pull/30)

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

- [zendframwork/zend-code#31](https://github.com/zendframework/zend-code/pull/31) updated license year.

## 2.6.2 - 2015-01-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#31](https://github.com/zendframework/zend-code/pull/31) updated license year.

## 2.6.1 - 2015-11-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#25](https://github.com/zendframework/zend-code/pull/25) changes the
  `doctrine/common` suggestion/dev-dependency to the more specific
  `doctrine/annotations` package (which is what is actually consumed).

## 2.6.0 - 2015-11-18

### Added

- [zendframwork/zend-code#12](https://github.com/zendframework/zend-code/pull/12) adds the ability to
  generate arrays using either long/standard syntax (`array(...)`) or short
  syntax (`[...]`). This can be accomplished by setting the value type to
  `ValueGenerator::TYPE_ARRAY_SHORT` instead of using `TYPE_ARRAY`.
  Additionally, you can use `TYPE_ARRAY_LONG` instead of `TYPE_ARRAY`; the two
  constants are synonyms.
- [zendframwork/zend-code#11](https://github.com/zendframework/zend-code/pull/11) adds the ability to
  generate interfaces via the new class `Laminas\Code\Generator\InterfaceGenerator`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframwork/zend-code#20](https://github.com/zendframework/zend-code/pull/20) updates
  the laminas-eventmanager dependency to `^2.6|^3.0`, and changes its
  internal usage to use the `triggerEventUntil()` signature.

## 2.5.3 - 2015-11-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframwork/zend-code#10](https://github.com/zendframework/zend-code/pull/10) removes a
  development dependency on zendframework/zend-version.
- [zendframwork/zend-code#23](https://github.com/zendframework/zend-code/pull/23) removes a
  requirement on laminas/laminas-stdlib. This results in a slight change in
  `Laminas\Code\Generator\ValueGenerator`: `setConstants()` and `getConstants()`
  can now receive/emit *either* an SPL `ArrayObject` or
  `Laminas\Stdlib\ArrayObject`. Since these are functionally equivalent, however,
  you will experience no change in behavior.

### Fixed

- Nothing.
