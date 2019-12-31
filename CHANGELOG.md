# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.3 - 2016-04-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-code#51](https://github.com/zendframework/zend-code/pull/51) updates the
  laminas-stdlib dependency to allow either 2.7 or 3.0 releases, as the
  functionality consumed maintains its API between releases.

## 2.6.2 - 2016-01-05

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
