# PHPUnit-Automock

### Project is currently a work in progress, do not use in production.

Automock is a highly opinionated extension to PHPUnit that will help in
reducing test boilerplate and writing maintainable code.

## General

Automocking is to be used upon Units with dependencies, in order to use it one must:

1. Register the Automock listener in [phpunit.xml](./phpunit.xml)
2. Inherit the Automock\UnitUnderTest
3. Define a unit in the testcase using the `@unit` annotation

With this; when the Test is executed Automock will automatically take the Unit and create
PHPUnit\Framework\MockObject\MockObject of all its dependencies. Instantiate the Unit
providing the MockObject's and proxy all public methods up into the test-case.
It will also proxy properties that reflect dependencies up unto the test-case,
regardless of their defined accessability.

The purpose if this is to:

* Greatly reduce the amount of boilerplate needed to test a Unit
* Reduce chances of improperly mocking an object
* Enforce the patterns of the automocking throughout the implementation
* Enforce only testing one Unit per TestCase

## Testing

* Install dependencies using `composer install`
* Run tests using `vendor/bin/phpunit`

## Enforced patterns

### Unit dependencies must not be primitives

A Unit may not depend on primitives, Automock will prevent this by halting your
test if your dependency is not a class/interface.

This is mainly to enforce the pattern of wrapping all primitives into validated
domain-specific value-objects.

These value-object should not be tested with Automock but in a common PHPUnit
TestCase, since they should not have any dependencies to mock, and should have
no side-effects.

### No ephemeral "dependencies"

A Unit may not depend on something which it does not actually require, such as
for instantiating a secondary class with this "dependency" as the argument. In
such a case the Unit should depend on the secondary class. E.g.

Automock will automatically fail:
```php
class A {
	private $c;

	public function __construct(B $b) {
		$this->c = new C($b);
	}
}
```

As it is highly likely it should have been:

```php

class C {
	private $b;

	public function __construct(B $b) {
		$this->b = $b;
	}
}

class A {
	private $c;

	public function __construct(C $c) {
		$this->c = $c;
	}
}
```

In addition the Unit must assign the dependency to a private property on the Unit.
No other class that uses the Unit should implictly be able to utilize/mutate
the dependecies of the Unit.
