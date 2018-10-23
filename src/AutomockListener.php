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

            $testCaseReflection = new ReflectionClass($test);
            $unitReflection = $this->getAutomockClassReflection($testCaseReflection);

            if (is_null($unitReflection)) {
                throw new AutomockException(
                    'Could not resolve Unit to test, did you forget the @unit annotation is TestCase?'
                );
            }

            $dependencies = $this->getUnitDependencies($test, $unitReflection);
            $unit = $unitReflection->newInstanceArgs($dependencies);

            $this->proxyProperties($test, $unit);
            $this->proxyMethods($test, $unitReflection, $unit);

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
                    return new ReflectionClass($className);
                }
            }
        }

        return null;
    }

    /**
     * Returns an array of mocked dependencies base on the
     * constructor arguments of the reflected unit
     */
    private function getUnitDependencies(Test $test, ReflectionClass $unitReflection): array
    {
        $constructor = $unitReflection->getConstructor();
        $constructorParameters = $constructor->getParameters();

        return array_map(function ($parameter) use ($test) {
            $type = (string)$parameter->getType();
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
