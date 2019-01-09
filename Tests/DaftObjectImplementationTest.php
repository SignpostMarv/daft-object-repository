<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use SignpostMarv\DaftObject;
use SignpostMarv\DaftObject\TypeUtilities;

class DaftObjectImplementationTest extends TestCase
{
    const NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION = 5;

    public function dataProviderImplementations() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/LinkedData/*.php' => 'SignpostMarv\\DaftObject\\LinkedData\\',
            ] as $glob => $ns
        ) {
            $files = glob(dirname(__DIR__) . $glob);
            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    class_exists($className = ($ns . pathinfo($file, PATHINFO_FILENAME))) &&
                    is_a($className, DaftObject\DaftObject::class, true)
                ) {
                    yield [$className];
                }
            }
        }
    }

    final public function dataProviderInvalidImplementations() : array
    {
        $out = [];

        $implementations = array_filter($this->InvalidImplementations(), 'is_string');

        foreach ($implementations as $arg) {
            if (class_exists($arg) && is_a($arg, DaftObject\DaftObject::class, true)) {
                $out[] = $arg;
            }
        }

        return $out;
    }

    final public function dataProviderNonAbstractImplementations() : Generator
    {
        /**
        * @var iterable<array>
        */
        $sources = $this->dataProviderImplementations();

        foreach ($sources as $args) {
            list($className) = $args;
            if (
                is_string($className) &&
                is_a($className, DaftObject\DaftObject::class, true) &&
                false === (($reflector = new ReflectionClass($className))->isAbstract())
            ) {
                yield [$className, $reflector];
            }
        }
    }

    final public function dataProviderNonAbstractGoodImplementations() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        /**
        * @var iterable<array>
        */
        $implementations = $this->dataProviderNonAbstractImplementations();

        foreach ($implementations as $args) {
            if (false === in_array($args[0] ?? null, $invalid, true)) {
                list($implementation) = $args;

                if ( ! is_string($implementation)) {
                    static::markTestSkipped(
                        'Index 0 retrieved from ' .
                        get_class($this) .
                        '::dataProviderNonAbstractImplementations must be a string'
                    );

                    return;
                } elseif ( ! is_subclass_of($implementation, DaftObject\DaftObject::class, true)) {
                    static::markTestSkipped(
                        'Index 0 retrieved from ' .
                        get_class($this) .
                        '::dataProviderNonAbstractImplementations must be an implementation of ' .
                        DaftObject\DaftObject::class
                    );

                    return;
                }

                /**
                * @var array
                */
                $properties = $implementation::DaftObjectProperties();

                $initialCount = count($properties);

                if (
                    $initialCount !== count(
                        array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                    )
                ) {
                    continue;
                }

                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodImplementationsWithMixedCaseProperties() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        /**
        * @var array<int, array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractImplementations();

        foreach ($implementations as $args) {
            /**
            * @var array{0:string, 1:ReflectionClass}
            */
            $args = $args;

            if (false === in_array($args[0] ?? null, $invalid, true)) {
                list($implementation) = $args;

                if (is_subclass_of($implementation, DaftObject\DaftObject::class, true)) {
                    /**
                    * @var array
                    */
                    $properties = $implementation::DaftObjectProperties();

                    $initialCount = count($properties);

                    if (
                        $initialCount === count(
                            array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                        )
                    ) {
                        $args[] = false;
                    } else {
                        $args[] = true;
                    }

                    yield $args;
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodImplementationsWithProperties() : Generator
    {
        /**
        * @var iterable<array>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            /**
            * @var scalar
            */
            $className = $args[0];

            if ( ! is_string($className)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractImplementations must be a string'
                );

                return;
            } elseif ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractGoodImplementations must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var array
            */
            $properties = $className::DaftObjectProperties();

            if (count($properties) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractDefinesOwnIdGoodImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementationsWithProperties();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            if (is_a($className, DaftObject\DefinesOwnIdPropertiesInterface::class, true)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodNullableImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementationsWithProperties();

        foreach ($implementations as $args) {
            /**
            * @var array{0:scalar, 1:ReflectionClass}
            */
            static::assertIsString($args[0]);

            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractGoodImplementations must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var array
            */
            $properties = $className::DaftObjectNullableProperties();

            if (count($properties) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodExportableImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            /**
            * @var array{0:scalar, 1:ReflectionClass}
            */
            $args = $args;

            static::assertIsString($args[0]);

            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractGoodImplementations must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var array
            */
            $exportables = $className::DaftObjectExportableProperties();

            /**
            * @var array
            */
            $properties = $className::DaftObjectProperties();

            if (
                count($exportables) > 0 &&
                count($properties) > 0
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodPropertiesImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            /**
            * @var array{0:scalar, 1:ReflectionClass}
            */
            $args = $args;

            static::assertIsString($args[0]);

            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractGoodImplementations must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var array
            */
            $properties = $className::DaftObjectProperties();

            if (count($properties) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGetterSetters() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractImplementations();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            /**
            * @var ReflectionClass
            */
            $reflector = $args[1];

            foreach ($reflector->getMethods() as $method) {
                if (preg_match('/^[GS]et[A-Z]/', $method->getName()) > 0) {
                    yield [$className, $method];
                }
            }
        }
    }

    final public function dataProviderGoodNonAbstractGetterSetters() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        /**
        * @var \Traversable<array<int, string|ReflectionMethod>>
        */
        $implementations = $this->dataProviderNonAbstractGetterSetters();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                continue;
            }

            /**
            * @var ReflectionMethod
            */
            $method = $args[1];

            if (false === in_array($className, $invalid, true)) {
                yield [$className, $method];
            }
        }
    }

    final public function dataProviderGoodNonAbstractGetterSettersNotId() : Generator
    {
        /**
        * @var iterable<array>
        */
        $implementations = $this->dataProviderGoodNonAbstractGetterSetters();

        foreach ($implementations as $args) {
            /**
            * @var array{0:scalar, 1:ReflectionClass}
            */
            $args = $args;

            static::assertIsString($args[0]);

            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderGoodNonAbstractGetterSetters must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var ReflectionClass
            */
            $reflector = $args[1];

            $property = mb_substr($reflector->getName(), 3);

            /**
            * @var array
            */
            $properties = $className::DaftObjectProperties();

            $defined = (
                in_array($property, $properties, true) ||
                in_array(lcfirst($property), $properties, true)
            );

            $definesOwnId = is_a(
                $className,
                DaftObject\DefinesOwnIdPropertiesInterface::class,
                true
            );

            if ( ! (false === $defined && $definesOwnId)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodSortableImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            if (
                isset($args[0]) &&
                is_string($args[0]) &&
                is_a($args[0], DaftObject\DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodNonSortableImplementations() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            if (
                isset($args[0]) &&
                is_string($args[0]) &&
                is_a($args[0], DaftObject\AbstractDaftObject::class, true) &&
                ! is_a($args[0], DaftObject\DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderFuzzingImplementations() : Generator
    {
        /**
        * @var \Traversable<array|null>
        */
        $implementations = $this->FuzzingImplementationsViaGenerator();

        foreach ($implementations as $args) {
            if (
                is_array($args) &&
                self::MIN_EXPECTED_ARRAY_COUNT === count($args) &&
                isset($args[0], $args[1]) &&
                is_string($args[0]) &&
                is_array($args[1]) &&
                is_a($args[0], DaftObject\DaftObject::class, true)
            ) {
                $validKeys = true;

                /**
                * @var array<int, string|int>
                */
                $args1keys = array_keys($args[1]);

                foreach ($args1keys as $shouldBeProperty) {
                    if (false === is_string($shouldBeProperty)) {
                        $validKeys = false;
                        break;
                    }
                }
                if ($validKeys) {
                    yield [$args[0], $args[1]];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzing() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass>>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            /**
            * @var ReflectionClass
            */
            $reflector = $args[1];

            /**
            * @var \Traversable<array<int, string|array>>
            */
            $fuzzingImplementations = $this->dataProviderFuzzingImplementations();

            foreach ($fuzzingImplementations as $fuzzingImplementationArgs) {
                /**
                * @var string
                */
                $implementation = $fuzzingImplementationArgs[0];

                /**
                * @var array
                */
                $args = $fuzzingImplementationArgs[1];

                if (is_a($className, $implementation, true)) {
                    /**
                    * @var DaftObject\DaftObject
                    */
                    $className = $className;

                    $getters = [];
                    $setters = [];

                    $properties = $className::DaftObjectProperties();

                    $initialCount = count($properties);

                    if (
                        $initialCount !== count(
                            array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                        )
                    ) {
                        continue;
                    }

                    foreach ($properties as $property) {
                        $propertyForMethod = ucfirst($property);
                        $getter = TypeUtilities::MethodNameFromProperty($propertyForMethod, false);
                        $setter = TypeUtilities::MethodNameFromProperty($propertyForMethod, true);

                        if ($reflector->hasMethod($getter)) {
                            /**
                            * @var ReflectionMethod
                            */
                            $getter = $reflector->getMethod($getter);

                            if ($getter->isPublic()) {
                                $getters[] = $property;
                            }
                        }

                        if ($reflector->hasMethod($setter)) {
                            /**
                            * @var ReflectionMethod
                            */
                            $setter = $reflector->getMethod($setter);

                            if ($setter->isPublic()) {
                                $setters[] = $property;
                            }
                        }
                    }

                    yield [$className, $reflector, $args, $getters, $setters];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSetters() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass|array>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzing();

        foreach ($implementations as $args) {
            if (count($args) < self::NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION) {
                continue;
            }

            if (count((array) $args[4]) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractNonWormGoodFuzzingHasSetters() : Generator
    {
        /**
        * @var \Traversable<array<int, string>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzingHasSetters();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $interfaceCheck = $args[0];

            if ( ! is_a($interfaceCheck, DaftObject\DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters() : Generator
    {
        /**
        * @var \Traversable<array<int, string>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzingHasSetters();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            if (
                false === is_a($className, DaftObject\DaftJson::class, true) &&
                is_a($className, DaftObject\AbstractArrayBackedDaftObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() : Generator
    {
        /**
        * @var \Traversable<array<int, mixed>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzingHasSetters();

        foreach ($implementations as $args) {
            if (count($args) < self::NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION) {
                continue;
            }

            /**
            * @var array<int, string>
            */
            $setters = $args[4];

            foreach ($setters as $property) {
                if (in_array($property, array_keys((array) $args[2]), true)) {
                    yield [$args[0], $args[1], $args[2], $args[3], $args[4], $property];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm() : Generator
    {
        /**
        * @var \Traversable<array<int, mixed>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty();

        foreach ($implementations as $args) {
            /**
            * @var string
            */
            $className = $args[0];

            if (is_a($className, DaftObject\DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
    ) : Generator {
        /**
        * @var iterable<array<int, mixed>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty();

        foreach ($implementations as $args) {
            if (count($args) <= self::NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION) {
                continue;
            }

            static::assertIsString($args[0]);
            static::assertIsString($args[5]);

            /**
            * @var string
            */
            $className = $args[0];

            if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
                static::markTestSkipped(
                    'Index 0 retrieved from ' .
                    get_class($this) .
                    '::dataProviderNonAbstractGoodFuzzingHasSettersPerProperty' .
                    ' must be an implementation of ' .
                    DaftObject\DaftObject::class
                );

                return;
            }

            /**
            * @var string
            */
            $property = $args[5];

            /**
            * @var array
            */
            $properties = $className::DaftObjectNullableProperties();

            if (
                false === in_array(
                    $property,
                    $properties,
                    true
                )
            ) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodImplementationsWithProperties
    */
    final public function testHasDefinedAllPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string|null>
        */
        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));

        foreach ($properties as $property) {
            static::assertIsString(
                $property,
                ($className . '::DaftObjectProperties()' . ' must return an array of strings')
            );
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodImplementationsWithMixedCaseProperties
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    */
    final public function testHasDefinedAllPropertiesCorrectlyExceptMixedCase(
        string $className,
        ReflectionClass $reflector,
        bool $hasMixedCase
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectProperties();

        $initialCount = count($properties);
        $postCount = count(array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR));

        if ($hasMixedCase) {
            static::assertLessThan($initialCount, $postCount);
        } else {
            static::assertSame($initialCount, $postCount);
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractDefinesOwnIdGoodImplementations
    */
    final public function testHasDefinedAllIdPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DefinesOwnIdPropertiesInterface::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DefinesOwnIdPropertiesInterface::class
            );

            return;
        }

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));

        /**
        * @var array<int, string|null>
        */
        $idProperties = (array) $className::DaftObjectIdProperties();

        foreach ($idProperties as $property) {
            static::assertIsString(
                $property,
                ($className . '::DaftObjectIdProperties()' . ' must return an array of strings')
            );
        }

        /**
        * @var array<int, string>
        */
        $idProperties = $idProperties;

        foreach ($idProperties as $property) {
            static::assertTrue(
                in_array($property, $properties, true),
                (
                    $className .
                    '::DaftObjectIdProperties() defines as property (' .
                    $property .
                    ') that is not defined on ' .
                    $className .
                    '::DaftObjectProperties()'
                )
            );
        }

        $initialCount = count($properties);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $properties),
                SORT_REGULAR
            )
        );

        $initialCount = count($idProperties);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $idProperties),
                SORT_REGULAR
            )
        );
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodNullableImplementations
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    */
    final public function testHasDefinedAllNullablesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string|null>
        */
        $nullables = $className::DaftObjectNullableProperties();

        foreach ($nullables as $nullable) {
            static::assertIsString(
                $nullable,
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' must return an array of strings'
                )
            );
        }

        /**
        * @var array<int, string>
        */
        $nullables = $nullables;

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectProperties();

        foreach ($nullables as $nullable) {
            static::assertTrue(
                in_array($nullable, $properties, true),
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' ' .
                    'a nullable property (' .
                    $nullable .
                    ') that was not defined as a property on ' .
                    $className .
                    '::DaftObjectProperties()'
                )
            );
        }

        if (count($properties) > 0 && 0 === count($nullables)) {
            foreach ($properties as $property) {
                $getter = TypeUtilities::MethodNameFromProperty($property, false);
                $setter = TypeUtilities::MethodNameFromProperty($property, true);

                if ($reflector->hasMethod($getter)) {
                    $method = $reflector->getMethod($getter);

                    static::assertTrue(
                        $method->hasReturnType(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $getter .
                            ' had no return type, cannot verify is not nullable.'
                        )
                    );

                    /**
                    * @var ReflectionType
                    */
                    $returnType = $method->getReturnType();

                    static::assertFalse(
                        $returnType->allowsNull(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $getter .
                            ' defines a nullable return type, but ' .
                            $className .
                            ' indicates no nullable properties!'
                        )
                    );
                }
                if ($reflector->hasMethod($setter)) {
                    $method = $reflector->getMethod($setter);

                    static::assertGreaterThan(
                        0,
                        $method->getNumberOfParameters(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $setter .
                            ' has no parameters, cannot verify is not nullable!'
                        )
                    );

                    foreach ($method->getParameters() as $param) {
                        static::assertFalse(
                            $param->allowsNull(),
                            (
                                $method->getDeclaringClass()->getName() .
                                '::' .
                                $setter .
                                ' defines a parameter that allows null, but ' .
                                $className .
                                ' indicates no nullable properties!'
                            )
                        );
                    }
                }
            }
        }

        $initialCount = count($nullables);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $nullables),
                SORT_REGULAR
            )
        );
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodExportableImplementations
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    */
    final public function testHasDefinedAllExportablesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string|null>
        */
        $exportables = $className::DaftObjectExportableProperties();

        foreach ($exportables as $exportable) {
            static::assertIsString(
                $exportable,
                (
                    $className .
                    '::DaftObjectExportableProperties()' .
                    ' must return an array of strings'
                )
            );
        }

        /**
        * @var array<int, string>
        */
        $exportables = $exportables;

        /**
        * @var array<int, string>
        */
        $properties = (array) $className::DaftObjectProperties();

        foreach ($exportables as $exportable) {
            static::assertTrue(
                in_array($exportable, $properties, true),
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' ' .
                    'a nullable property (' .
                    $exportable .
                    ') that was not defined as a property on ' .
                    $className .
                    '::DaftObjectProperties()'
                )
            );
        }

        if (0 === count($exportables) && count($properties) > 0) {
            static::assertFalse(
                is_a($className, DaftObject\DaftJson::class, true),
                (
                    'Implementations with no exportables should not implement ' .
                    DaftObject\DaftJson::class
                )
            );
        }

        $initialCount = count($exportables);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $exportables),
                SORT_REGULAR
            )
        );
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodPropertiesImplementations
    */
    final public function testHasDefinedImplementationCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectProperties();

        /**
        * @var array<int, string>
        */
        $nullables = $className::DaftObjectNullableProperties();

        /**
        * @var array<int, string>
        */
        $exportables = $className::DaftObjectExportableProperties();

        foreach ($properties as $property) {
            $getter = TypeUtilities::MethodNameFromProperty($property, false);
            $setter = TypeUtilities::MethodNameFromProperty($property, true);

            $hasAny = $reflector->hasMethod($getter) || $reflector->hasMethod($setter);

            static::assertTrue(
                $hasAny,
                (
                    $className .
                    ' must implement at least a getter `' .
                    $className .
                    '::' .
                    $getter .
                    '()` or setter `' .
                    $className .
                    '::' .
                    $setter .
                    '()` for ' .
                    $property .
                    ' on ' .
                    $className .
                    '.'
                )
            );

            $reflectorGetter = null;
            $getterPublic = (
                $reflector->hasMethod($getter) &&
                ($reflectorGetter = $reflector->getMethod($getter))->isPublic()
            );

            $reflectorSetter = null;
            $setterPublic = (
                $reflector->hasMethod($setter) &&
                ($reflectorSetter = $reflector->getMethod($setter))->isPublic()
            );

            $anyPublic = $getterPublic || $setterPublic;

            $isNullable = in_array($property, $nullables, true);

            static::assertTrue(
                $anyPublic,
                (
                    $className .
                    ' must implement at least a public getter or setter for ' .
                    $className .
                    '::$' .
                    $property
                )
            );

            if ($getterPublic) {
                /**
                * @var ReflectionMethod
                */
                $reflectorGetter = $reflectorGetter;

                static::assertSame(
                    0,
                    $reflectorGetter->getNumberOfParameters(),
                    (
                        $reflectorGetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorGetter->getName() .
                        '() must not have any parameters.'
                    )
                );
                static::assertTrue(
                    $reflectorGetter->hasReturnType(),
                    (
                        $reflectorGetter->getNumberOfParameters() .
                        $reflectorGetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorGetter->getName() .
                        '() must have a return type.'
                    )
                );

                $returnType = null;

                /**
                * @var ReflectionType
                */
                $returnType = $reflectorGetter->getReturnType();

                static::assertTrue(
                    ('void' !== $returnType->__toString()),
                    (
                        $reflectorGetter->getNumberOfParameters() .
                        $reflectorGetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorGetter->getName() .
                        '() must have a non-void return type.'
                    )
                );

                if ($isNullable) {
                    static::assertTrue(
                        $returnType->allowsNull(),
                        (
                            $reflectorGetter->getNumberOfParameters() .
                            $reflectorGetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorGetter->getName() .
                            '() must have a nullable return type.'
                        )
                    );
                }
            }

            if ($setterPublic) {
                /**
                * @var ReflectionMethod
                */
                $reflectorSetter = $reflectorSetter;

                static::assertSame(
                    1,
                    $reflectorSetter->getNumberOfParameters(),
                    (
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must have only one parameter.'
                    )
                );

                static::assertTrue(
                    $reflectorSetter->hasReturnType(),
                    (
                        $reflectorSetter->getNumberOfParameters() .
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must specify a void return type.'
                    )
                );

                /**
                * @var ReflectionType
                */
                $returnType = $reflectorSetter->getReturnType();

                static::assertSame(
                    'void',
                    $returnType->__toString(),
                    (
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must specify a void return type, "' .
                        $returnType->__toString() .
                        '" found.'
                    )
                );

                /**
                * @var ReflectionType|null
                */
                $type = ($reflectorSetter->getParameters()[0])->getType();

                if ($type instanceof ReflectionType) {
                    static::assertSame(
                        $type->allowsNull(),
                        $isNullable,
                        (
                            $reflectorSetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorSetter->getName() .
                            '() must have a ' .
                            ($isNullable ? '' : 'non-') .
                            'nullable type when specified.'
                        )
                    );
                }
            }
        }

        /**
        * @var scalar|array|object|null
        */
        $propertiesChangeProperties = $className::DaftObjectPropertiesChangeOtherProperties();

        static::assertIsArray($propertiesChangeProperties);

        $propertiesChangeProperties = (array) $propertiesChangeProperties;

        $propertiesChangePropertiesCount = count($propertiesChangeProperties);

        $propertiesChangeProperties = array_filter(
            array_filter(
                array_filter($propertiesChangeProperties, 'is_string', ARRAY_FILTER_USE_KEY),
                'is_array'
            ),
            function (string $maybe) use ($properties) : bool {
                return in_array($maybe, $properties, true);
            },
            ARRAY_FILTER_USE_KEY
        );

        static::assertCount($propertiesChangePropertiesCount, $propertiesChangeProperties);

        $propertiesChangePropertiesCount = count($propertiesChangeProperties, COUNT_RECURSIVE);

        $propertiesChangeProperties = array_map(
            function (array $in) use ($properties) : array {
                return array_values(array_unique(array_filter(
                    array_filter(
                        array_filter(
                            $in,
                            'is_string'
                        ),
                        'is_int',
                        ARRAY_FILTER_USE_KEY
                    ),
                    function (string $property) use ($properties) : bool {
                        return in_array($property, $properties, true);
                    }
                )));
            },
            $propertiesChangeProperties
        );

        static::assertTrue(
            $propertiesChangePropertiesCount === count(
                $propertiesChangeProperties,
                COUNT_RECURSIVE
            )
        );
    }

    /**
    * @dataProvider dataProviderGoodNonAbstractGetterSettersNotId
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testHasAllGettersAndSettersDefinedAsProperties(
        string $className,
        ReflectionMethod $reflector
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        $property = mb_substr($reflector->getName(), 3);

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectProperties();

        $defined = (
            in_array($property, $properties, true) ||
            in_array(lcfirst($property), $properties, true)
        );

        static::assertTrue(
            $defined,
            (
                $reflector->getDeclaringClass()->getName() .
                '::' .
                $reflector->getName() .
                '() was not defined in ' .
                $className .
                '::DaftObjectProperties()'
            )
        );
    }

    /**
    * @param array<string, mixed> $args
    * @param array<int, string> $getters
    * @param array<int, string> $setters
    *
    * @dataProvider dataProviderNonAbstractNonWormGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlank(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var DaftObject\DaftObject
        */
        $obj = new $className($args);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            ($className . '::ChangedProperties() must be empty after instantiation')
        );

        /**
        * @var DaftObject\DaftObject
        */
        $obj = new $className([]);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            ($className . '::ChangedProperties() must be empty after instantiation')
        );

        $settersNotNull = [];

        /**
        * @var array<string, array<int, string>>
        */
        $otherProperties = $className::DaftObjectPropertiesChangeOtherProperties();

        foreach ($setters as $setterProperty) {
            /**
            * @var array<int, string>
            */
            $propertiesExpectedToBeChanged = [
                $setterProperty,
            ];

            /**
            * @var array<int, string>
            */
            $propertiesExpectedNotToBeChanged = [];

            /**
            * @var array<int, string>
            */
            $checkingProperties = array_merge(
                $propertiesExpectedToBeChanged,
                $propertiesExpectedNotToBeChanged
            );

            if (isset($otherProperties[$setterProperty])) {
                /**
                * @var array<int, string>
                */
                $propertiesExpectedToBeChanged = $otherProperties[$setterProperty];

                if ( ! in_array($setterProperty, $propertiesExpectedToBeChanged, true)) {
                    $propertiesExpectedNotToBeChanged[] = $setterProperty;
                }
            }

            foreach (
                $checkingProperties as $property
            ) {
                static::assertFalse(
                    $obj->HasPropertyChanged($property),
                    (
                        $className .
                        '::$' .
                        $property .
                        ' should not be marked as changed' .
                        ' when instantiating from blank.'
                    )
                );
            }

            if (isset($args[$setterProperty])) {
                $obj->$setterProperty = $args[$setterProperty];

                foreach ($propertiesExpectedToBeChanged as $property) {
                    static::assertTrue(
                        $obj->HasPropertyChanged($property),
                        ($className . '::$' . $property . ' should be marked as changed.')
                    );
                }

                foreach ($propertiesExpectedNotToBeChanged as $property) {
                    static::assertFalse(
                        $obj->HasPropertyChanged($property),
                        ($className . '::$' . $property . ' should not be marked as changed.')
                    );
                }

                $obj->MakePropertiesUnchanged($setterProperty);

                foreach (
                    $checkingProperties as $property
                ) {
                    static::assertFalse(
                        $obj->HasPropertyChanged($property),
                        (
                            $className .
                            '::$' .
                            $property .
                            ' should be marked as unchanged after calling ' .
                            $className .
                            '::MakePropertiesUnchanged()'
                        )
                    );
                }
            }
        }

        /**
        * @var DaftObject\DaftObject
        */
        $obj = new $className([]);

        foreach ($setters as $property) {
            $obj->$property = $args[$property];

            if (in_array($property, $getters, true)) {
                /**
                * @var scalar|array|object|null
                */
                $expecting = $args[$property];

                /**
                * @var scalar|array|object|null
                */
                $compareTo = $obj->$property;

                if (
                    ($expecting !== $compareTo) &&
                    ($expecting instanceof DateTimeImmutable) &&
                    ($compareTo instanceof DateTimeImmutable) &&
                    get_class($expecting) === get_class($compareTo)
                ) {
                    $expecting = $expecting->format('cu');
                    $compareTo = $compareTo->format('cu');
                }

                static::assertSame($expecting, $compareTo);
            }
        }

        $debugInfo = $this->VarDumpDaftObject($obj);

        $regex = '/' . static::RegexForObject($obj) . '$/s';

        static::assertRegExp($regex, str_replace(["\n"], ' ', $debugInfo));

        foreach ($setters as $setterProperty) {
            /**
            * @var array<int, string>
            */
            $propertiesExpectedToBeChanged = [
                $setterProperty,
            ];

            if (isset($otherProperties[$setterProperty])) {
                /**
                * @var array<int, string>
                */
                $propertiesExpectedToBeChanged = $otherProperties[$setterProperty];
            }

            foreach ($propertiesExpectedToBeChanged as $property) {
                static::assertTrue(
                    in_array($property, $obj->ChangedProperties(), true),
                    ($className . '::ChangedProperties() must contain changed properties')
                );
            }
        }

        /**
        * @var array<int, string>
        */
        $properties = $className::DaftObjectNullableProperties();

        foreach ($properties as $property) {
            $checkGetterIsNull = (
                in_array($property, $getters, true) &&
                isset($args[$property]) &&
                false === is_null($args[$property])
            );

            if ($obj->HasPropertyChanged($property)) {
                if ($checkGetterIsNull) {
                    static::assertTrue(
                        isset($obj->$property),
                        (
                            $className .
                            '::__isset(' .
                            $property .
                            ') must return true after ' .
                            $className .
                            '::$' .
                            $property .
                            ' has been set'
                        )
                    );
                }

                unset($obj->$property);
            }

            if ($checkGetterIsNull) {
                static::assertNull(
                    $obj->$property,
                    ($className . '::$' . $property . ' must be null after being unset')
                );
            }
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlankThenJsonSerialiseMaybeFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        $obj = new $className($args);

        if ($obj instanceof DaftObject\DaftJson) {
            if ( ! is_subclass_of($className, DaftObject\DaftJson::class, true)) {
                static::markTestSkipped(
                    'Argument 1 passed to ' .
                    __METHOD__ .
                    ' must be an implementation of ' .
                    DaftObject\DaftJson::class
                );

                return;
            }

            $obj->jsonSerialize();

            $json = json_encode($obj);

            static::assertIsString(
                $json,
                (
                    'Instances of ' .
                    get_class($obj) .
                    ' should resolve to a string when passed to json_encode()'
                )
            );

            /**
            * @var array|bool
            */
            $decoded = json_decode((string) $json, true);

            static::assertIsArray(
                $decoded,
                (
                    'JSON-encoded implementations of ' .
                    DaftObject\DaftJson::class .
                    ' (' .
                    get_class($obj) .
                    ')' .
                    ' must decode to an array!'
                )
            );

            /**
            * @var DaftObject\DaftJson
            */
            $objFromJson = $className::DaftObjectFromJsonArray($decoded);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftObject\DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );

            /**
            * @var DaftObject\DaftJson
            */
            $objFromJson = $className::DaftObjectFromJsonString($json);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftObject\DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );
        } else {
            if (method_exists($obj, 'jsonSerialize')) {
                $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
                $this->expectExceptionMessage(sprintf(
                    '%s does not implement %s',
                    $className,
                    DaftObject\DaftJson::class
                ));

                $obj->jsonSerialize();

                return;
            }
            static::markTestSkipped(sprintf(
                '%s does not implement %s or %s::jsonSerialize()',
                $className,
                DaftObject\DaftJson::class,
                $className
            ));

            return;
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromArrayFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if ( ! is_a($className, DaftObject\AbstractArrayBackedDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\AbstractArrayBackedDaftObject::class
            );

            return;
        }

        $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftObject\DaftJson::class
        ));

        $className::DaftObjectFromJsonArray([]);
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingDaftObjectIsNotDaftJson(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        /**
        * @var DaftObject\DaftObject
        */
        $obj = new $className($args);

        static::expectException(DaftObject\ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftObject\DaftJson::class
        ));

        DaftObject\JsonTypeUtilities::ThrowIfDaftObjectObjectNotDaftJson($obj);
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromStringFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\AbstractArrayBackedDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\AbstractArrayBackedDaftObject::class
            );

            return;
        }

        $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftObject\DaftJson::class
        ));

        $className::DaftObjectFromJsonString('{}');
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSetters
    *
    * @depends testHasDefinedAllExportablesCorrectly
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlankThenJsonSerialiseMaybePropertiesFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        if (is_a($className, DaftObject\DaftJson::class, true)) {
            /**
            * @var array<int, string>
            */
            $exportables = (array) $className::DaftObjectExportableProperties();

            /**
            * @var array<int, string>
            */
            $propertyNames = (array) $className::DaftObjectJsonPropertyNames();

            $jsonProps = [];

            /**
            * @var array<int|string, string|null>
            */
            $properties = $className::DaftObjectJsonProperties();

            foreach ($properties as $k => $v) {
                $prop = $v;

                if (is_string($k)) {
                    static::assertIsString(
                        $v,
                        sprintf(
                            (
                                '%s::DaftObjectJsonProperties()' .
                                ' ' .
                                'key-value pairs' .
                                ' ' .
                                'must be either array<int, string>' .
                                ' or ' .
                                'array<string, string>'
                            ),
                            $className
                        )
                    );

                    /**
                    * @var string
                    */
                    $v = $v;

                    if ('[]' === mb_substr($v, -2)) {
                        $v = mb_substr($v, 0, -2);
                    }

                    static::assertTrue(
                        class_exists($v),
                        sprintf(
                            (
                                'When %s::DaftObjectJsonProperties()' .
                                ' ' .
                                'key-value pair is array<string, string>' .
                                ' ' .
                                'the value must refer to a class.'
                            ),
                            $className
                        )
                    );

                    static::assertTrue(
                        is_a($v, DaftObject\DaftJson::class, true),
                        sprintf(
                            (
                                'When %s::DaftObjectJsonProperties()' .
                                ' ' .
                                'key-value pair is array<string, string>' .
                                ' ' .
                                'the value must be an implementation of %s'
                            ),
                            $className,
                            DaftObject\DaftJson::class
                        )
                    );

                    $prop = $k;
                }

                static::assertContains(
                    $prop,
                    $exportables,
                    sprintf(
                        (
                            'Properties listed in' .
                            ' ' .
                            '%s::DaftObjectJsonProperties() must also be' .
                            ' ' .
                            'listed in %s::DaftObjectExportableProperties()'
                        ),
                        $className,
                        $className
                    )
                );

                static::assertContains(
                    $prop,
                    $propertyNames,
                    sprintf(
                        (
                            'Properties listed in' .
                            ' ' .
                            '%s::DaftObjectJsonProperties() must also be' .
                            ' ' .
                            'listed in %s::DaftObjectJsonPropertyNames()'
                        ),
                        $className,
                        $className
                    )
                );

                $jsonProps[] = $prop;
            }

            foreach ($propertyNames as $prop) {
                static::assertContains(
                    $prop,
                    $propertyNames,
                    sprintf(
                        (
                            'Properties listed in' .
                            ' ' .
                            '%s::DaftObjectJsonPropertyNames() must also be' .
                            ' ' .
                            'listed or referenced in' .
                            ' ' .
                            '%s::DaftObjectJsonProperties()'
                        ),
                        $className,
                        $className
                    )
                );
            }

            $initialCount = count($propertyNames);

            static::assertCount(
                $initialCount,
                array_unique(
                    array_map('mb_strtolower', $propertyNames),
                    SORT_REGULAR
                )
            );
        } elseif (is_a($className, DaftObject\AbstractArrayBackedDaftObject::class, true)) {
            $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
            $this->expectExceptionMessage(sprintf(
                '%s does not implement %s',
                $className,
                DaftObject\DaftJson::class
            ));

            /**
            * @var DaftObject\DaftJson
            */
            $className = $className;

            $className::DaftObjectJsonProperties();
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        $obj = new $className($args, true);

        $this->expectException(DaftObject\PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->$property = $args[$property];
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm
    *
    * @depends testHasDefinedImplementationCorrectly
    */
    final public function testProviderNonAbstractGoodFuzzingHasSettersPerPropertyWormAfterCreate(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        $obj = new $className([], true);

        $obj->$property = $args[$property];

        $this->expectException(DaftObject\PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->$property = $args[$property];
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable
    */
    final public function testNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        if ( ! is_subclass_of($className, DaftObject\AbstractDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\AbstractDaftObject::class
            );

            return;
        } elseif (
            ! $reflector->isAbstract() &&
            in_array($property, $setters, true)
        ) {
            /**
            * @var ReflectionMethod
            */
            $method = $reflector->getMethod('NudgePropertyValue');

            $method->setAccessible(true);

            $this->expectException(DaftObject\PropertyNotNullableException::class);
            $this->expectExceptionMessage(sprintf(
                'Property not nullable: %s::$%s',
                $className,
                $property
            ));

            $method->invoke(new $className(), $property, null);
        }
    }

    final public function dataProviderDaftObjectCreatedByArray() : Generator
    {
        /**
        * @var \Traversable<array|null>
        */
        $implementations = $this->dataProviderNonAbstractGoodImplementations();

        foreach ($implementations as $args) {
            if (
                is_array($args) &&
                count($args) >= 1 &&
                is_string($args[0]) &&
                is_a($args[0], DaftObject\DaftObjectCreatedByArray::class, true)
            ) {
                yield [$args[0], true];
                yield [$args[0], false];
            }
        }
    }

    final public function DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
    ) : Generator {
        /**
        * @var iterable<array<int, string>>
        */
        $sources = $this->dataProviderImplementations();

        foreach ($sources as $args) {
            if (
                is_a($args[0], DaftObject\AbstractDaftObject::class, true) &&
                ! is_a(
                    $args[0],
                    DaftObject\DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                    true
                )
            ) {
                yield [$args[0]];
            }
        }
    }

    /**
    * @dataProvider dataProviderDaftObjectCreatedByArray
    */
    final public function testConstructorArrayKeys(string $className, bool $writeAll) : void
    {
        if ( ! is_a($className, DaftObject\DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftObject::class
            );

            return;
        }

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Properties must be strings!');

        $object = new $className([1], $writeAll);
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodSortableImplementations
    */
    public function testSortableImplementation(string $className) : void
    {
        if ( ! is_a($className, DaftObject\DaftSortableObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\DaftSortableObject::class
            );

            return;
        }

        /**
        * @var scalar|array|object|resource|null
        */
        $publicOrProtected = $className::DaftObjectPublicOrProtectedGetters();

        static::assertIsArray($publicOrProtected);

        /**
        * @var array<int|string, scalar|array|object|null>
        */
        $publicOrProtected = $publicOrProtected;

        foreach ($publicOrProtected as $k => $v) {
            static::assertIsInt($k);
            static::assertIsString($v);
        }

        /**
        * @var array<int|string, scalar|array|object|null>
        */
        $properties = $className::DaftSortableObjectProperties();

        foreach ($properties as $k => $v) {
            static::assertIsInt($k);
            static::assertIsString($v);

            /**
            * @var string
            */
            $v = $v;

            /**
            * @var string
            */
            $expectedMethod = TypeUtilities::MethodNameFromProperty($v, false);

            static::assertTrue(in_array($v, $publicOrProtected, true));
            static::assertTrue(method_exists($className, $expectedMethod));
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodNonSortableImplementations
    */
    public function testNotSortableImplementation(string $className) : void
    {
        if ( ! is_a($className, DaftObject\AbstractDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\AbstractDaftObject::class
            );

            return;
        }

        static::assertFalse(is_subclass_of(
            $className,
            DaftObject\DaftSortableObject::class,
            true
        ));

        static::expectException(DaftObject\ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftObject\DaftSortableObject::class
        ));

        $className::DaftSortableObjectProperties();
    }

    public function testSortableObject() : void
    {
        $a = new DaftObject\SortableReadWrite([
            'Foo' => 'a',
            'Bar' => 1.0,
            'Baz' => 1,
            'Bat' => false,
        ]);
        $b = new DaftObject\SortableReadWrite([
            'Foo' => 'b',
            'Bar' => 2.0,
            'Baz' => 2,
            'Bat' => true,
        ]);

        static::assertSame(0, $a->CompareToDaftSortableObject($a));
        static::assertSame(0, $b->CompareToDaftSortableObject($b));
        static::assertSame(-1, $a->CompareToDaftSortableObject($b));
        static::assertSame(1, $b->CompareToDaftSortableObject($a));
    }

    public function testNotSortableObject() : void
    {
        $a = new DaftObject\ReadWrite([
            'Foo' => 'a',
            'Bar' => 1.0,
            'Baz' => 1,
            'Bat' => false,
        ]);
        $b = new DaftObject\SortableReadWrite([
            'Foo' => 'b',
            'Bar' => 2.0,
            'Baz' => 2,
            'Bat' => true,
        ]);

        static::expectException(DaftObject\ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(sprintf(
            '%s does not implement %s',
            DaftObject\ReadWrite::class,
            DaftObject\DaftSortableObject::class
        ));

        $a->CompareToDaftSortableObject($b);
    }

    /**
    * @dataProvider DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues
    */
    public function testNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
        string $implementation
    ) : void {
        if ( ! is_a($implementation, DaftObject\AbstractDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject\AbstractDaftObject::class
            );

            return;
        }

        static::expectException(DaftObject\ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            $implementation .
            ' does not implement ' .
            DaftObject\DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class
        );

        $implementation::DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues();
    }

    /**
    * @psalm-suppress ForbiddenCode
    */
    final protected function VarDumpDaftObject(DaftObject\DaftObject $obj) : string
    {
        ob_start();
        var_dump($obj);

        return (string) ob_get_clean();
    }

    protected static function RegexForObject(DaftObject\DaftObject $obj) : string
    {
        /**
        * @var array<string, scalar|array|DaftObject\DaftObject|null>
        */
        $props = [];

        /**
        * @var array<int, string>
        */
        $exportables = $obj::DaftObjectExportableProperties();

        foreach ($exportables as $prop) {
            $expectedMethod = TypeUtilities::MethodNameFromProperty($prop, false);
            if (
                $obj->__isset($prop) &&
                method_exists($obj, $expectedMethod) &&
                (new ReflectionMethod($obj, $expectedMethod))->isPublic()
            ) {
                /**
                * @var scalar|array|DaftObject\DaftObject|null
                */
                $res = $obj->$expectedMethod();

                $props[$prop] = $res;
            }
        }

        return static::RegexForArray(get_class($obj), $props);
    }

    /**
    * @param array<string, scalar|object|array|null> $props
    */
    protected static function RegexForArray(string $className, array $props) : string
    {
        $regex =
            '(?:class |object\()' .
            preg_quote($className, '/') .
            '[\)]{0,1}#' .
            '\d+ \(' .
            preg_quote((string) count($props), '/') .
            '\) \{.+';

        foreach ($props as $prop => $val) {
            $regex .=
                ' (?:public ' .
                preg_quote('$' . $prop, '/') .
                '|' .
                preg_quote('["' . $prop . '"]', '/') .
                ')[ ]{0,1}' .
                preg_quote('=', '/') .
                '>.+' .
                static::RegexForVal($val) .
                '.+';
        }

        $regex .= '.+';

        return $regex;
    }

    /**
    * @param mixed $val
    */
    protected static function RegexForVal($val) : string
    {
        if (is_array($val)) {
            $out = '(?:';

            /**
            * @var (scalar|object|array|null)[]
            */
            $val = $val;

            foreach ($val as $v) {
                $out .= static::RegexForVal($v);
            }

            $out .= ')';

            return $out;
        } elseif ($val instanceof DateTimeImmutable) {
            return
                '(?:class |object){0,1}' .
                '\({0,1}' .
                preg_quote(DateTimeImmutable::class, '/') .
                '(?:\:\:__set_state\(array\(|\)?#\d+ \(\d+\) \{)' .
                '\s+(?:\["|\'|public \$)date(?:"\]|\'){0,1}\s*=>\s+(?:\'[^\']+\',|string\(\d+\) \"[^"]+\")' .
                '\s+(?:\["|\'|public \$)timezone_type(?:"\]|\'){0,1}\s*=>\s+(?:int\(){0,1}\d+(?:\)){0,1},{0,1}' .
                '\s+(?:\["|\'|public \$)timezone(?:"\]|\'){0,1}\s*=>\s+(?:\'[^\']+\',|string\(\d+\) \"[^"]+\")' .
                '\s*(?:\)\)|\})';
        }

        return
            (
                is_int($val)
                    ? 'int'
                    : (
                        is_bool($val)
                            ? 'bool'
                            : (
                                is_float($val)
                                    ? '(?:float|double)'
                                    : (is_object($val) ? '' : preg_quote(gettype($val), '/'))
                            )
                    )
            ) .
            (
                ($val instanceof DaftObject\DaftObject)
                    ? ('(?:' . static::RegexForObject($val) . ')')
                    : preg_quote(
                        (
                            '(' .
                            (
                                is_string($val)
                                    ? mb_strlen($val, '8bit')
                                    : (is_numeric($val) ? (string) $val : var_export($val, true))
                            ) .
                            ')' .
                            (is_string($val) ? (' "' . $val . '"') : '')
                        ),
                        '/'
                    )
        );
    }

    /**
    * @return array<int, string>
    */
    protected function InvalidImplementations() : array
    {
        return [
            DaftObject\NudgesIncorrectly::class,
            DaftObject\ReadOnlyBad::class,
            DaftObject\ReadOnlyBadDefinesOwnId::class,
            DaftObject\ReadOnlyInsuficientIdProperties::class,
        ];
    }

    protected function FuzzingImplementationsViaArray() : array
    {
        return [
            [
                DaftObject\AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
            ],
            [
                DaftObject\AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
            ],
            [
                DaftObject\AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
            ],
            [
                DaftObject\PasswordHashTestObject::class,
                [
                    'password' => 'foo',
                ],
            ],
            [
                DaftObject\ReadWriteJsonJson::class,
                [
                    'json' => new DaftObject\ReadWriteJson([
                        'Foo' => 'Foo',
                        'Bar' => 1.0,
                        'Baz' => 2,
                        'Bat' => true,
                    ]),
                ],
            ],
            [
                DaftObject\ReadWriteJsonJson::class,
                [
                    'json' => new DaftObject\ReadWriteJson([
                        'Foo' => 'Foo',
                        'Bar' => 2.0,
                        'Baz' => 3,
                        'Bat' => false,
                    ]),
                ],
            ],
            [
                DaftObject\ReadWriteJsonJsonArray::class,
                [
                    'json' => [
                        new DaftObject\ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 3.0,
                            'Baz' => 4,
                            'Bat' => null,
                        ]),
                        new DaftObject\ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 1.0,
                            'Baz' => 2,
                            'Bat' => true,
                        ]),
                        new DaftObject\ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 2.0,
                            'Baz' => 3,
                            'Bat' => false,
                        ]),
                        new DaftObject\ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 3.0,
                            'Baz' => 4,
                            'Bat' => null,
                        ]),
                    ],
                ],
            ],
            [
                DaftObject\IntegerIdBasedDaftObject::class,
                [
                    'Foo' => 1,
                ],
            ],
            [
                DaftObject\DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DaftObject\DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        0
                    )),
                ],
            ],
            [
                DaftObject\DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DaftObject\DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        1
                    )),
                ],
            ],
        ];
    }

    protected function FuzzingImplementationsViaGenerator() : Generator
    {
        yield from $this->FuzzingImplementationsViaArray();
    }

    protected function SortableFuzzingImplementationsViaGenerator() : Generator
    {
        /**
        * @var \Traversable<array<int, string|ReflectionClass|array>>
        */
        $implementations = $this->dataProviderNonAbstractGoodFuzzing();

        foreach ($implementations as $args) {
            if (
                is_string($args[0]) &&
                is_a($args[0], DaftObject\DaftSortableObject::class)
            ) {
                yield $args;
            }
        }
    }
}
