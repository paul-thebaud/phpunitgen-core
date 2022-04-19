# Change Log

## 2.0.1

**Added**

- docker-composer for PHP for testing purposes

## 2.0.0

**Changed**

- Add support for PHP `~8.0.12` and `~8.1.0`.
- Drop support for PHP `7`.
- When param or return `native` type is `mixed` or `object`, use the documentation type if available.
- Test generator will now ensure public and testable methods exists in class in `canGenerate`.
- Handle doc type resolving inside PhpUnitGen since BetterReflection V5 does not do this anymore.

**Fixed**

- Laravel Controller test generator will correctly handle API controllers.

## 1.x.x

See the [1.x.x CHANGELOG](https://github.com/paul-thebaud/phpunitgen-core/blob/1.x.x/CHANGELOG.md).
