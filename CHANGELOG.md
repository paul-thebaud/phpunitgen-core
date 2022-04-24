# Change Log

## 3.0.0

**Added**

- **[BREAKING]** Add `testClassFinal`, `testClassStrictTypes` and `testClassTypedProperties` in `Config` interface.
- **[BREAKING]** Add `TypeFactory` interface with default implementation.
- Add `testClassFinal` option and update rendering to create final test class (see #19).
- Add `testClassStrictTypes` option and update rendering to prepend strict types declaration test class (see #19).
- Add `testClassTypedProperties` option and update rendering to strictly type test class properties (see #20).

**Changed**

- **[BREAKING]** Change `makeForProperty` signature in `DocumentationFactory` interface.
- Test class properties are now declared as private properties instead of protected.

## 2.x.x

See the [2.x.x CHANGELOG](https://github.com/paul-thebaud/phpunitgen-core/blob/2.x.x/CHANGELOG.md).

## 1.x.x

See the [1.x.x CHANGELOG](https://github.com/paul-thebaud/phpunitgen-core/blob/1.x.x/CHANGELOG.md).
