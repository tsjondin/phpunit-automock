<?php

declare(strict_types=1);

namespace Automock;

use ReflectionClass;
use ReflectionObject;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\Test;

class AutomockListener
{
    const AM_ANNOTATION_KEY = '@unit';

    /**
     * The hook that sets up automocking if the current TestCase
     * is extending the AutomockTestCase
     */
    public function __construct(Test $test)
    {
        if (is_a($test, AutomockTestCase::class)) {
            try {
                $testCaseReflection = new ReflectionClass($test);
                $unitReflection = $this->getAutomockClassReflection($testCaseReflection);

                $dependencies = $this->getUnitDependencies($test, $unitReflection);
                $unit = $unitReflection->newInstanceArgs($dependencies);

                $this->proxyProperties($test, $unit);
                $this->proxyMethods($test, $unitReflection, $unit);

            } catch (AutomockException $e) {
                $test->fail("\r\nAutomock: " . $e->getMessage() . "\r\n" . $e->getHint()  . "\r\n");
            }
        }
    }


    private function buildMock(Test $test, $type)
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
     * @throws AutomockException
     */
    private function getAutomockClassReflection(ReflectionClass $testCaseReflection)
    {

        $block = trim(trim($testCaseReflection->getDocComment(), "/*/ \r\n"));
        $lines = preg_split("/\r\n|\n/", $block);

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, AutomockListener::AM_ANNOTATION_KEY) === 0) {
                $split = preg_split("/\s+/", $line);
                if (count($split) === 2) {
                    $className = $split[1];
                    if (!class_exists($className)) {
                        throw new AutomockException(
                            sprintf(
                                "Could not find class '%s' in AutomockTestCase '%s'",
                                $className,
                                $testCaseReflection->getName()
                            ),
                            'Is the autoloading properly set up or is it only a spelling error?'
                        );
                    }
                    return new ReflectionClass($className);
                }
            }
        }

        throw new AutomockException(
            sprintf(
                "Could not find @unit annotation in AutomockTestCase '%s'",
                $testCaseReflection->getName()
            )
        );
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
     * This only works in conjunction with the AutomockTestCase
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
     * Reveals all unit propertis as proxied members on the
     * TestCase class that is testing the unit
     */
    private function proxyProperties(Test $test, $unit)
    {
        $activeReflectedUnit = new ReflectionObject($unit);
        $properties = $activeReflectedUnit->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $property->setAccessible(true);
            $test->{$name} = $property->getValue($unit);
        }
    }

}
