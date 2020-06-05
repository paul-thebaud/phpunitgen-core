# Change Log

## 1.1.1

**Changed**

- Support for `tightenco/collect` `5.8` to `7.0`.

## 1.1.0

**Added**

- Support for PHP `7.1+` (instead of `7.2+`).

## 1.0.1

**Changed**

- Indicates `^4.0` instead of `4.0.x-dev` for `BetterReflection`

## 1.0.0-beta3

**Changed**

- Indicates `4.0.x-dev` instead of `dev-master` for `BetterReflection`

## 1.0.0-beta2

**Added**

- Allow `^5.0` of `phpdocumentor/reflection-docblock`.

## 1.0.0-beta1

Release the package as a beta stage.

## 1.0.0-alpha10

**Added**

- Require `roave/better-reflection` with `dev-master` version.

## 1.0.0-alpha9

**Added**

- Add a test generator for Laravel Controller.
- Add a test generator for Laravel Job.
- Add a test generator for Laravel Listener.
- Add a test generator for Laravel Resource.
- Add a test generator for Laravel Rule.
- Add the previous test generator to possible delegate test generator's solutions.

**Changed**

- When there is no constructor inside tested class, instantiate without parameters instead of affecting a `null` value.

## 1.0.0-alpha8

**Added**

- `PropertyFactory` must implement new method `makeCustom`.
- Support for method's parameters/return types retrieved from phpDoc.
- Add a test generator for Laravel Broadcasting channel.
- Add a test generator for Laravel command.
- Add a Laravel command and channel test generators in Delegate test generator.

**Changed**

- `Renderer` implementations must return the instance on each `visit...()` methods.
- `Renderable` implementations must return the instance of Renderer on `accept()`.

**Removed**

- Removed useless `$reflectionProperty` property on `TestMethod`.
- Removed useless `VISIBILITY...` constants on `TestMethod`.

## 1.0.0-alpha7

**Changed**

- Code parser will throw an exception if invalid syntax is detected.

## 1.0.0-alpha6

**Changed**

- Allow `phpdocumentor/reflection-docblock` dependency `^4.1` instead of `4.3`.

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
