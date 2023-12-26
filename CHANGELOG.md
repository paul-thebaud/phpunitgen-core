# Change Log

## 4.1.0

**Added**

- Add `php@8.3` support (see paul-thebaud/phpunitgen-console#13).

## 4.0.0

**Added**

- Add `phpHeaderDoc` option to generate a documentation block in generated file header (closes #30).

**Changed**

- **BREAKING** Drop support for PHP 8.0.
- Change PHPUnit methods calls (`markTestIncomplete`, etc.) from instance to static calls (closes #29).
- Remove `setAccessible` calls (methods are accessible by default since PHP 8.1) (closes #27).

## 3.1.0

**Added**

- Add compatibility with `php@8.2` and `roave/better-reflection@^6.0`.

## 3.0.0

**Added**

- **BREAKING** Add `testClassFinal`, `testClassStrictTypes` and `testClassTypedProperties` in `Config` interface.
- **BREAKING** Add `TypeFactory` interface with default implementation.
- Add `testClassFinal` option and update rendering to create final test class (see #19).
- Add `testClassStrictTypes` option and update rendering to prepend strict types declaration test class (see #19).
- Add `testClassTypedProperties` option and update rendering to strictly type test class properties (see #20).
- Support for `tightenco/collect` version `^9.0`.

**Changed**

- **BREAKING** Change `makeForProperty` signature in `DocumentationFactory` interface.
- Test class properties are now declared as private properties instead of protected.

## 2.x.x

See the [2.x.x CHANGELOG](https://github.com/paul-thebaud/phpunitgen-core/blob/2.x.x/CHANGELOG.md).

## 1.x.x

See the [1.x.x CHANGELOG](https://github.com/paul-thebaud/phpunitgen-core/blob/1.x.x/CHANGELOG.md).
