<?php

declare(strict_types=1);

namespace Automock;

use ReflectionClass;
use ReflectionObject;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Test;

class AutomockListener
{
    const AUTOMOCK_ANNOTATION_KEY = '@unit';

    /**
     * Sets up automocking for the TestCase if it is extending the UnitUnderTest
     */
    public function __construct(Test $test)
    {
        if (is_a($test, UnitUnderTest::class)) {
            try {

                $testCaseReflection = new ReflectionClass($test);
                $unitReflection = $this->getAutomockClassReflection($testCaseReflection);

                $dependencies = $this->getUnitDependencies($test, $unitReflection);
                $unit = $unitReflection->newInstanceArgs($dependencies);

                $this->proxyProperties($test, $unit, $dependencies);
                $this->proxyMethods($test, $unitReflection, $unit);

            } catch (AutomockException $e) {
                $test->fail("\r\nAutomock: " . $e->getMessage() . "\r\n" . $e->getHint()  . "\r\n");
            }
        }
    }

    /**
     * Returns a MockObject for a given Test $test and class/interface of $type
     */
    private function buildMock(Test $test, $type): MockObject
    {
        $builder = new MockBuilder($test, $type);
        return $builder
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }

    /**
     * Rough-handedly parse a docblock in order to find annotations
     *
     * Finds anything starting with @ and sets it as a key in an assoc. array with
     * any trailing values as the value. If there are no trailing values it simply
     * sets it to true
     */
    private function parseDocBlock(string $docblock): array
    {
        $block = trim(trim($docblock, "/*/ \r\n"));
        $lines = preg_split("/\r\n|\n/", $block);

        return array_reduce($lines, function (array $annotations, string $line): array {
            $line = trim($line);
            if (strpos($line, '@') === 0) {
                $split = preg_split("/\s+/", $line);
                $key = array_shift($split);
                $value = (count($split) > 0) ? implode(' ', $split) : true;
                return array_merge([$key => $value], $annotations);
            }
            return $annotations;
        }, []);
    }

    /**
     * @throws AutomockException If no unit annotation could be found
     */
    private function findUnitClassName(ReflectionClass $testCaseReflection): string
    {
        $annotations = $this->parseDocBlock($testCaseReflection->getDocComment());

        if (isset($annotations[AutomockListener::AUTOMOCK_ANNOTATION_KEY])) {
            return $annotations[AutomockListener::AUTOMOCK_ANNOTATION_KEY];
        }

        throw new AutomockException(
            sprintf(
                "Could not find @unit annotation in UnitUnderTest '%s'",
                $testCaseReflection->getName()
            )
        );
    }

    /**
     * @throws AutomockException If the Unit specified could not be found
     */
    private function getAutomockClassReflection(ReflectionClass $testCaseReflection): ReflectionClass
    {
        $className = $this->findUnitClassName($testCaseReflection);
        if (!class_exists($className)) {
            throw new AutomockException(
                sprintf(
                    "Could not find class '%s' in UnitUnderTest '%s'",
                    $className,
                    $testCaseReflection->getName()
                ),
                'Is the autoloading properly set up or is it only a spelling error?'
            );
        }
        return new ReflectionClass($className);
    }

    /**
     * Returns an array of mocked dependencies base on the
     * constructor arguments of the reflected unit
     */
    private function getUnitDependencies(Test $test, ReflectionClass $unitReflection): array
    {
        $constructor = $unitReflection->getConstructor();
        $constructorParameters = $constructor->getParameters();

        return array_map(function ($parameter) use ($test, $unitReflection) {
            $type = (string)$parameter->getType();
            if (!class_exists($type) && !interface_exists($type)) {
                throw new AutomockPatternException(
                    sprintf(
                        "'%s' has a dependency '%s' which could not be resolved as a class or interface. (resolved as an '%s').",
                        $unitReflection->getName(),
                        $parameter->getName(),
                        $type
                    ),
                    "Primitive values must be wrapped in validated domain-specific value-objects"
                );
            }
            return $this->buildMock($test, $type);
        }, $constructorParameters);
    }

    /**
     * Reveals all unit methods as proxied methods on the
     * TestCase class that is testing the unit
     *
     * This only works in conjunction with the UnitUnderTest
     * class and its magic __call method
     */
    private function proxyMethods(Test $test, ReflectionClass $unitReflection, $unit)
    {
        $methods = $unitReflection->getMethods();
        foreach ($methods as $method) {
            if ($method->isPublic() && !$method->isStatic()) {
                $name = $method->getName();
                $test->__defineAMMethod($unit, $name);
            }
        }
    }

    /**
     * Reveals properties on the unit that reflect dependencies as public
     * properties on the TestCase
     */
    private function proxyProperties(Test $test, $unit, array $dependencies)
    {
        $activeReflectedUnit = new ReflectionObject($unit);
        $properties = $activeReflectedUnit->getProperties();
        foreach ($properties as $property) {

            $property->setAccessible(true);
            $value = $property->getValue($unit);
            $property->setAccessible(false);

            $name = $property->getName();

            foreach ($dependencies as $dependency) {
                if ($dependency === $value) {
                    $test->{$name} = $value;
                    continue;
                }
            }
        }
    }

}
