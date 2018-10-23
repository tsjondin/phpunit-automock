### Project is currently a work in progress, do not use in production.

Automocking is to be used upon Units with dependencies, in order to use it one must:

1. Define a PHPUnit listener that instantiates automock (see [the demo listener](./tests/MyAutomockListener.php))
2. Inherit the Automock\AutomockTestCase
3. Define a unit in the testcase using the `@unit` annotation

With this; when the Test is executed Automock will automatically take the Unit and create
PHPUnit\Framework\MockObject\MockObject of all its dependencies. Instantiate the Unit
providing the MockObject's and proxy all public methods and all properties (regardless of
accessability) into the TestCase.

The purpose if this is to:

* Greatly reduce the amount of boilerplate needed to test a Unit
* Reduce chances of improperly mocking an object
* Enforce the patterns of the automocking throughout the implementation
* Enforce only testing one Unit per TestCase

## Testing

* Install dependencies using `composer install`
* Run tests using `vendor/bin/phpunit`
