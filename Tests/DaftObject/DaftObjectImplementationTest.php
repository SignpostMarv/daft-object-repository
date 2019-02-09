<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\AbstractTestObject;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues;
use SignpostMarv\DaftObject\DaftObjectNotDaftJsonBadMethodCallException;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\DateTimeImmutableTestObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\IntegerIdBasedDaftObject;
use SignpostMarv\DaftObject\NudgesIncorrectly;
use SignpostMarv\DaftObject\PasswordHashTestObject;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\PropertyNotRewriteableException;
use SignpostMarv\DaftObject\ReadOnlyBad;
use SignpostMarv\DaftObject\ReadOnlyBadDefinesOwnId;
use SignpostMarv\DaftObject\ReadOnlyInsuficientIdProperties;
use SignpostMarv\DaftObject\ReadWriteJson;
use SignpostMarv\DaftObject\ReadWriteJsonJson;
use SignpostMarv\DaftObject\ReadWriteJsonJsonArray;
use SignpostMarv\DaftObject\SortableReadWrite;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template T as DaftObject
*/
class DaftObjectImplementationTest extends TestCase
{
    const NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION = 5;

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftObject>}, mixed, void>
    */
    public function dataProviderImplementations() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/LinkedData/*.php' => 'SignpostMarv\\DaftObject\\LinkedData\\',
            ] as $glob => $ns
        ) {
            $files = glob(__DIR__ . '/../..' . $glob);
            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    class_exists($className = ($ns . pathinfo($file, PATHINFO_FILENAME))) &&
                    is_a($className, DaftObject::class, true)
                ) {
                    yield [$className];
                }
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractImplementations() : Generator
    {
        foreach ($this->dataProviderImplementations() as $args) {
            if ( ! (($reflector = new ReflectionClass($args[0]))->isAbstract())) {
                yield [$args[0], $reflector];
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementations() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            if (false === in_array($args[0] ?? null, $invalid, true)) {
                $reflector = new ReflectionClass($args[0]);

                if ($reflector->isAbstract() || $reflector->isInterface()) {
                    static::markTestSkipped(
                        'Index 0 retrieved from ' .
                        get_class($this) .
                        '::dataProviderNonAbstractImplementations must be a' .
                        ' non-abstract, non-interface  implementation of ' .
                        DaftObject::class
                    );

                    return;
                }

                $properties = $args[0]::DaftObjectProperties();

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

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:bool}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementationsWithMixedCaseProperties() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            if (false === in_array($args[0] ?? null, $invalid, true)) {
                list($implementation) = $args;

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

                /**
                * @psalm-var array{0:class-string<T>, 1:ReflectionClass, 2:bool}
                */
                $args = $args;

                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementationsWithProperties() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (count($args[0]::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DefinesOwnIdPropertiesInterface>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractDefinesOwnIdGoodImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            if (is_a($args[0], DefinesOwnIdPropertiesInterface::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodNullableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            if (count($args[0]::DaftObjectNullableProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodExportableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (
                count($args[0]::DaftObjectExportableProperties()) > 0 &&
                count($args[0]::DaftObjectProperties()) > 0
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodPropertiesImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (count($args[0]::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderNonAbstractGetterSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            foreach ($args[1]->getMethods() as $method) {
                if (preg_match('/^[GS]et[A-Z]/', $method->getName()) > 0) {
                    yield [$args[0], $method];
                }
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderGoodNonAbstractGetterSetters() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractGetterSetters() as $args) {
            if (false === in_array($args[0], $invalid, true)) {
                yield [$args[0], $args[1]];
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderGoodNonAbstractGetterSettersNotId() : Generator
    {
        foreach ($this->dataProviderGoodNonAbstractGetterSetters() as $args) {
            $property = mb_substr($args[1]->getName(), 3);

            $properties = $args[0]::DaftObjectProperties();

            if (
                ! (
                    ! (
                        in_array($property, $properties, true) ||
                        in_array(lcfirst($property), $properties, true)
                    ) &&
                    is_a(
                        $args[0],
                        DefinesOwnIdPropertiesInterface::class,
                        true
                    )
                )
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DaftSortableObject>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodSortableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (is_a($args[0], DaftSortableObject::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&AbstractDaftObject>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodNonSortableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (
                is_a($args[0], AbstractDaftObject::class, true) &&
                ! is_a($args[0], DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzing() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            foreach ($this->FuzzingImplementationsViaGenerator() as $fuzzingImplementationArgs) {
                if (is_a($args[0], $fuzzingImplementationArgs[0], true)) {
                    /**
                    * @psalm-var class-string<T>
                    */
                    $args[0] = $args[0];

                    $getters = [];
                    $setters = [];

                    $properties = $args[0]::DaftObjectProperties();

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

                        if (
                            $args[1]->hasMethod($getter) &&
                            $args[1]->getMethod($getter)->isPublic()
                        ) {
                            $getters[] = $property;
                        }

                        if (
                            $args[1]->hasMethod($setter) &&
                            $args[1]->getMethod($setter)->isPublic()
                        ) {
                            $setters[] = $property;
                        }
                    }

                    yield [$args[0], $args[1], $fuzzingImplementationArgs[1], $getters, $setters];
                }
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            if (count((array) $args[4]) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractNonWormGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if ( ! is_a($args[0], DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&AbstractArrayBackedDaftObject>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if (
                false === is_a($args[0], DaftJson::class, true) &&
                is_a($args[0], AbstractArrayBackedDaftObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            foreach ($args[4] as $property) {
                if (in_array($property, array_keys((array) $args[2]), true)) {
                    yield [$args[0], $args[1], $args[2], $args[3], $args[4], $property];
                }
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DaftObjectWorm>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as $args) {
            if (is_a($args[0], DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
    ) : Generator {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as $args) {
            if ( ! in_array($args[5], $args[0]::DaftObjectNullableProperties(), true)) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodImplementationsWithProperties
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasDefinedAllPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
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
    *
    * @psalm-param class-string<DaftObject> $className
    */
    final public function testHasDefinedAllPropertiesCorrectlyExceptMixedCase(
        string $className,
        ReflectionClass $reflector,
        bool $hasMixedCase
    ) : void {
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
    * @psalm-param class-string<\SignpostMarv\DaftObject\SuitableForRepositoryType> $className
    *
    * @dataProvider dataProviderNonAbstractDefinesOwnIdGoodImplementations
    */
    final public function testHasDefinedAllIdPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));

        $idProperties = (array) $className::DaftObjectIdProperties();

        static::assertGreaterThan(0, count($idProperties));

        foreach ($idProperties as $property) {
            static::assertIsString(
                $property,
                ($className . '::DaftObjectIdProperties()' . ' must return an array of strings')
            );
        }

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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
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
                is_a($className, DaftJson::class, true),
                (
                    'Implementations with no exportables should not implement ' .
                    DaftJson::class
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
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

        $propertiesChangeProperties = $className::DaftObjectPropertiesChangeOtherProperties();

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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
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
    * @param array<string, scalar|array|object|null> $args
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        /**
        * @var DaftObject
        */
        $obj = new $className($args);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            ($className . '::ChangedProperties() must be empty after instantiation')
        );

        /**
        * @var DaftObject
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
            $propertiesExpectedNotToBeChanged = $className::DaftObjectProperties();

            if (isset($otherProperties[$setterProperty])) {
                $propertiesExpectedToBeChanged = $otherProperties[$setterProperty];
                $propertiesExpectedNotToBeChanged = array_filter(
                    $propertiesExpectedNotToBeChanged,
                    function (string $maybe) use ($otherProperties, $setterProperty) : bool {
                        return ! in_array($maybe, $otherProperties[$setterProperty], true);
                    }
                );
            }

            $propertiesExpectedNotToBeChanged = array_filter(
                $propertiesExpectedNotToBeChanged,
                function (string $maybe) use ($propertiesExpectedToBeChanged) : bool {
                    return ! in_array($maybe, $propertiesExpectedToBeChanged, true);
                }
            );

            /**
            * @var array<int, string>
            */
            $checkingProperties = array_merge(
                $propertiesExpectedToBeChanged,
                $propertiesExpectedNotToBeChanged
            );

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
                $obj->__set($setterProperty, $args[$setterProperty]);

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
            }

            $obj->MakePropertiesUnchanged(...$checkingProperties);

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

        /**
        * @var DaftObject
        */
        $obj = new $className([]);

        $propertiesExpectedToBeChanged = [];

        foreach ($setters as $property) {
            if ( ! isset($args[$property])) {
                continue;
            }

            $obj->__set($property, $args[$property]);

            if (isset($otherProperties[$property])) {
                $propertiesExpectedToBeChanged = array_merge(
                    $propertiesExpectedToBeChanged,
                    $otherProperties[$property]
                );
            } else {
                $propertiesExpectedToBeChanged[] = $property;
            }

            if (in_array($property, $getters, true)) {
                /**
                * @var scalar|array|object|null
                */
                $expecting = $args[$property];

                $compareTo = $obj->__get($property);

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
                        $obj->__isset($property),
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

                $obj->__unset($property);
            }

            if ($checkGetterIsNull) {
                static::assertNull(
                    $obj->__get($property),
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        $obj = new $className($args);

        if ($obj instanceof DaftJson) {
            if ( ! is_subclass_of($className, DaftJson::class, true)) {
                static::markTestSkipped(
                    'Argument 1 passed to ' .
                    __METHOD__ .
                    ' must be an implementation of ' .
                    DaftJson::class
                );

                return;
            }

            /**
            * @var DaftJson
            */
            $className = $className;

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
            $decoded = json_decode($json, true);

            static::assertIsArray(
                $decoded,
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' (' .
                    get_class($obj) .
                    ')' .
                    ' must decode to an array!'
                )
            );

            /**
            * @var array
            */
            $decoded = $decoded;

            /**
            * @var DaftJson
            */
            $objFromJson = $className::DaftObjectFromJsonArray($decoded);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );

            /**
            * @var DaftJson
            */
            $objFromJson = $className::DaftObjectFromJsonString($json);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );
        } else {
            if (method_exists($obj, 'jsonSerialize')) {
                $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
                $this->expectExceptionMessage(sprintf(
                    '%s does not implement %s',
                    $className,
                    DaftJson::class
                ));

                $obj->jsonSerialize();

                return;
            }
            static::markTestSkipped(sprintf(
                '%s does not implement %s or %s::jsonSerialize()',
                $className,
                DaftJson::class,
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
        if ( ! is_a($className, AbstractArrayBackedDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                AbstractArrayBackedDaftObject::class
            );

            return;
        }

        $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftJson::class
        ));

        $className::DaftObjectFromJsonArray([]);
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
        if ( ! is_subclass_of($className, AbstractArrayBackedDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                AbstractArrayBackedDaftObject::class
            );

            return;
        }

        $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftJson::class
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
        if (is_a($className, DaftJson::class, true)) {
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
                        is_a($v, DaftJson::class, true),
                        sprintf(
                            (
                                'When %s::DaftObjectJsonProperties()' .
                                ' ' .
                                'key-value pair is array<string, string>' .
                                ' ' .
                                'the value must be an implementation of %s'
                            ),
                            $className,
                            DaftJson::class
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
        } elseif (is_a($className, AbstractArrayBackedDaftObject::class, true)) {
            $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
            $this->expectExceptionMessage(sprintf(
                '%s does not implement %s',
                $className,
                DaftJson::class
            ));

            /**
            * @var DaftJson
            */
            $className = $className;

            $className::DaftObjectJsonProperties();
        }
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    *
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        $obj = new $className($args, true);

        $this->expectException(PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__set($property, $args[$property]);
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    *
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
        if ( ! is_subclass_of($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        $obj = new $className([], true);

        $obj->__set($property, $args[$property]);

        $this->expectException(PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__set($property, $args[$property]);
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
        if ( ! is_subclass_of($className, AbstractDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                AbstractDaftObject::class
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

            $this->expectException(PropertyNotNullableException::class);
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
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (
                is_array($args) &&
                count($args) >= 1 &&
                is_string($args[0]) &&
                is_a($args[0], DaftObjectCreatedByArray::class, true)
            ) {
                yield [$args[0], true];
                yield [$args[0], false];
            }
        }
    }

    final public function DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
    ) : Generator {
        foreach ($this->dataProviderImplementations() as $args) {
            if (
                is_a($args[0], AbstractDaftObject::class, true) &&
                ! is_a(
                    $args[0],
                    DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
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
        if ( ! is_a($className, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Properties must be strings!');

        $object = new $className([1], $writeAll);
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodSortableImplementations
    *
    * @psalm-param class-string<DaftSortableObject> $className
    */
    public function testSortableImplementation(string $className) : void
    {
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
    *
    * @psalm-param class-string<AbstractDaftObject> $className
    */
    public function testNotSortableImplementation(string $className) : void
    {
        static::assertFalse(is_subclass_of(
            $className,
            DaftSortableObject::class,
            true
        ));
    }

    public function testSortableObject() : void
    {
        $a = new SortableReadWrite([
            'Foo' => 'a',
            'Bar' => 1.0,
            'Baz' => 1,
            'Bat' => false,
        ]);
        $b = new SortableReadWrite([
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

    /**
    * @dataProvider DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues
    */
    public function testNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
        string $implementation
    ) : void {
        if ( ! is_a($implementation, AbstractDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                AbstractDaftObject::class
            );

            return;
        }

        static::expectException(ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            $implementation .
            ' does not implement ' .
            DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class
        );

        $implementation::DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues();
    }

    /**
    * @return array<int, string>
    *
    * @psalm-return array<int, class-string<DaftObject>>
    */
    final public function dataProviderInvalidImplementations() : array
    {
        return [
            NudgesIncorrectly::class,
            ReadOnlyBad::class,
            ReadOnlyBadDefinesOwnId::class,
            ReadOnlyInsuficientIdProperties::class,
        ];
    }

    /**
    * @psalm-suppress ForbiddenCode
    */
    final protected function VarDumpDaftObject(DaftObject $obj) : string
    {
        ob_start();
        var_dump($obj);

        return (string) ob_get_clean();
    }

    protected static function RegexForObject(DaftObject $obj) : string
    {
        /**
        * @var array<string, scalar|array|DaftObject|null>
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
                $props[$prop] = $obj->__get($prop);
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
                ($val instanceof DaftObject)
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
    * @psalm-return array<int, array{0:class-string<T>, 1:array<string, scalar|array|object|null>}>
    */
    protected function FuzzingImplementationsViaArray() : array
    {
        return [
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
            ],
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
            ],
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
            ],
            [
                PasswordHashTestObject::class,
                [
                    'password' => 'foo',
                ],
            ],
            [
                ReadWriteJsonJson::class,
                [
                    'json' => new ReadWriteJson([
                        'Foo' => 'Foo',
                        'Bar' => 1.0,
                        'Baz' => 2,
                        'Bat' => true,
                    ]),
                ],
            ],
            [
                ReadWriteJsonJson::class,
                [
                    'json' => new ReadWriteJson([
                        'Foo' => 'Foo',
                        'Bar' => 2.0,
                        'Baz' => 3,
                        'Bat' => false,
                    ]),
                ],
            ],
            [
                ReadWriteJsonJsonArray::class,
                [
                    'json' => [
                        new ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 3.0,
                            'Baz' => 4,
                            'Bat' => null,
                        ]),
                        new ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 1.0,
                            'Baz' => 2,
                            'Bat' => true,
                        ]),
                        new ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 2.0,
                            'Baz' => 3,
                            'Bat' => false,
                        ]),
                        new ReadWriteJson([
                            'Foo' => 'Foo',
                            'Bar' => 3.0,
                            'Baz' => 4,
                            'Bat' => null,
                        ]),
                    ],
                ],
            ],
            [
                IntegerIdBasedDaftObject::class,
                [
                    'Foo' => 1,
                ],
            ],
            [
                DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        0
                    )),
                ],
            ],
            [
                DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        1
                    )),
                ],
            ],
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:array<string, scalar|array|object|null>}, mixed, void>
    */
    protected function FuzzingImplementationsViaGenerator() : Generator
    {
        yield from $this->FuzzingImplementationsViaArray();
    }

    protected function SortableFuzzingImplementationsViaGenerator() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            if (
                is_a($args[0], DaftSortableObject::class)
            ) {
                yield $args;
            }
        }
    }
}
