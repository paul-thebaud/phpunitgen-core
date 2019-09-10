# Change Log

## Not release yet

**Added**

- `DelegateTestGenerator` which choose a generator for the current `ReflectionClass`.
- `ContainerFactory` which creates a container from a `Config` instance.

**Changed**

- `DelegateTestGenerator` is the new default test generator in configuration.
- `CoreServiceProvider` will not throw an exception if there is missing definitions.
- `CoreServiceProvider` is now under `Container` namespace instead of `Providers.

**Removed**

- `CoreServiceProvider::REQUIRED_CONTRACTS` to only use `provides property.

## 1.0.0-alpha

**Added**

- All core features of PhpUnitGen with two test generators.
- `BasicTestGenerator` is the default one in configuration.
