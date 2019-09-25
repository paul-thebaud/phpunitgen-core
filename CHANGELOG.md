# Change Log

## 1.0.0-alpha5

**Changed**

- New `context` option, used in `DelegateTestGenerator` to know if it is a Laravel project instead of using `class_exists`.

## 1.0.0-alpha4

**Changed**

- `Str` helper class is no longer internal.
- Fix documentation rendering (replace `/*` with `/**`).
- `Application` class renamed to `CoreApplication`.
- `ContainerFactory` class renamed to `CoreContainerFactory`.
- `LaravelTestGenerator` for any class when Laravel project is declared (Application class exists).
- `ClassFactory` has two new methods to implement (`getTestBaseNamespace` and `getTestSubNamespace`).
- `DelegateTestGenerator` introduction, which contains the `getDelegate` method.
- `TestGenerator` contains a new method `getClassFactory` used for path generation.

## 1.0.0-alpha3

**Changed**

- `php` dependency changed from `~7.2` to `^7.2`.
- `tightenco/collect` dependency changed from `^5.8` to `^6.0`.
- `mockery/mockery` dependency changed from `^1.0` to `^1.2`.
- `phpunit/phpunit` dependency changed from `^8.1` to `^8.3`.

## 1.0.0-alpha2

**Added**

- `DelegateTestGenerator` which choose a generator for the current `ReflectionClass`.
- `ContainerFactory` which creates a container from a `Config` instance.

**Changed**

- `DelegateTestGenerator` is the new default test generator in configuration.
- `CoreServiceProvider` will not throw an exception if there is missing definitions.
- `CoreServiceProvider` is now under `Container` namespace instead of `Providers.

**Removed**

- `CoreServiceProvider::REQUIRED_CONTRACTS` to only use `provides property.

## 1.0.0-alpha1

**Added**

- All core features of PhpUnitGen with two test generators.
- `BasicTestGenerator` is the default one in configuration.
