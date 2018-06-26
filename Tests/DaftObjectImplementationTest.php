<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Generator;
use ReflectionClass;
use ReflectionMethod;
use SignpostMarv\DaftObject;

class DaftObjectImplementationTest extends TestCase
{
    public function dataProviderImplementations() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/*.php' => 'SignpostMarv\\DaftObject\\',
            ] as $glob => $ns
        ) {
            $files = glob(dirname(__DIR__) . $glob);
            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    class_exists(
                        $className = (
                            $ns .
                            pathinfo($file, PATHINFO_FILENAME)
                        )
                    ) &&
                    is_a($className, DaftObject\DaftObject::class, true)
                ) {
                    yield [
                        $className,
                    ];
                }
            }
        }
    }

    final public function dataProviderInvalidImplementations() : array
    {
        $out = [];

        foreach ($this->InvalidImplementations() as $arg) {
            if (
                is_string($arg) &&
                class_exists($arg) &&
                is_a($arg, DaftObject\DaftObject::class, true)
            ) {
                $out[] = $arg;
            }
        }

        return $out;
    }

    final public function dataProviderNonAbstractImplementations() : Generator
    {
        foreach ($this->dataProviderImplementations() as [$className]) {
            if (
                is_string($className) &&
                is_a($className, DaftObject\DaftObject::class, true) &&
                false === (
                    (
                        $reflector = new ReflectionClass($className)
                    )->isAbstract()
                )
            ) {
                yield [
                    $className,
                    $reflector,
                ];
            }
        }
    }

    final public function dataProviderNonAbstractGoodImplementations() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach (
            $this->dataProviderNonAbstractImplementations() as [
                $className,
                $reflector,
            ]
        ) {
            if (false === in_array($className, $invalid, true)) {
                yield [
                    $className,
                    $reflector,
                ];
            }
        }
    }

    final public function dataProviderNonAbstractGoodImplementationsWithProperties() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            $className = $args[0];

            if (count($className::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractDefinesOwnIdGoodImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            $className = $args[0];

            if (is_a($className, DaftObject\DefinesOwnIdPropertiesInterface::class, true)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodNullableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            list($className) = $args;

            if (count($className::DaftObjectNullableProperties()) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodExportableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            list($className) = $args;

            if (
                count($className::DaftObjectExportableProperties()) > 0 &&
                count($className::DaftObjectProperties()) > 0
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodPropertiesImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            list($className) = $args;

            if (count($className::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGetterSetters() : Generator
    {
        foreach (
            $this->dataProviderNonAbstractImplementations() as [
                $className,
                $reflector,
            ]
        ) {
            foreach (
                $reflector->getMethods() as $method
            ) {
                if (
                    preg_match('/^[GS]et[A-Z]/', $method->getName()) > 0
                ) {
                    yield [
                        $className,
                        $method,
                    ];
                }
            }
        }
    }

    final public function dataProviderGoodNonAbstractGetterSetters() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach (
            $this->dataProviderNonAbstractGetterSetters() as [
                $className,
                $method,
            ]
        ) {
            if (false === in_array($className, $invalid, true)) {
                yield [
                    $className,
                    $method,
                ];
            }
        }
    }

    final public function dataProviderGoodNonAbstractGetterSettersNotId() : Generator
    {
        foreach ($this->dataProviderGoodNonAbstractGetterSetters() as $args) {
            list($className, $reflector) = $args;

            $property = mb_substr($reflector->getName(), 3);

            $properties = $className::DaftObjectProperties();

            $defined = (
                in_array($property, $properties, true) ||
                in_array(
                    lcfirst($property),
                    $properties,
                    true
                )
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

    final public function dataProviderFuzzingImplementations() : Generator
    {
        foreach ($this->FuzzingImplementationsViaGenerator() as $args) {
            if (
                is_array($args) &&
                self::MIN_EXPECTED_ARRAY_COUNT === count($args) &&
                isset($args[0], $args[1]) &&
                is_string($args[0]) &&
                is_array($args[1]) &&
                is_a($args[0], DaftObject\DaftObject::class, true)
            ) {
                $validKeys = true;
                foreach (array_keys($args[1]) as $shouldBeProperty) {
                    if (false === is_string($shouldBeProperty)) {
                        $validKeys = false;
                        break;
                    }
                }
                if ($validKeys) {
                    yield [
                        $args[0],
                        $args[1],
                    ];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzing() : Generator
    {
        foreach (
            $this->dataProviderNonAbstractGoodImplementations() as [
                $className,
                $reflector,
            ]
        ) {
            foreach (
                $this->dataProviderFuzzingImplementations() as [
                    $implementation,
                    $args,
                ]
            ) {
                if (is_a($className, $implementation, true)) {
                    /**
                    * @var DaftObject\DaftObject $className
                    */
                    $className = $className;

                    $getters = [];
                    $setters = [];

                    foreach (
                        $className::DaftObjectProperties() as $property
                    ) {
                        $propertyForMethod = ucfirst($property);
                        $getter = 'Get' . $propertyForMethod;
                        $setter = 'Set' . $propertyForMethod;

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

                    yield [
                        $className,
                        $reflector,
                        $args,
                        $getters,
                        $setters,
                    ];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSetters(
    ) : Generator {
        foreach (
            $this->dataProviderNonAbstractGoodFuzzing() as [
                $className,
                $reflector,
                $args,
                $getters,
                $setters,
            ]
        ) {
            if (count($setters) > 0) {
                yield [
                    $className,
                    $reflector,
                    $args,
                    $getters,
                    $setters,
                ];
            }
        }
    }

    final public function dataProviderNonAbstractNonWormGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            list($interfaceCheck) = $args;

            if ( ! is_a($interfaceCheck, DaftObject\DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            list($className) = $args;

            if (
                false === is_a($className, DaftObject\DaftJson::class, true) &&
                is_a(
                    $className,
                    DaftObject\AbstractArrayBackedDaftObject::class,
                    true
                )
            ) {
                yield $args;
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerProperty(
    ) : Generator {
        foreach (
            $this->dataProviderNonAbstractGoodFuzzingHasSetters() as [
                $className,
                $reflector,
                $args,
                $getters,
                $setters,
            ]
        ) {
            foreach ($setters as $property) {
                if (in_array($property, array_keys($args), true)) {
                    yield [
                        $className,
                        $reflector,
                        $args,
                        $getters,
                        $setters,
                        $property,
                    ];
                }
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm(
    ) : Generator {
        foreach (
            $this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as [
                $className,
                $reflector,
                $args,
                $getters,
                $setters,
                $property,
            ]
        ) {
            if (is_a($className, DaftObject\DaftObjectWorm::class, true)) {
                yield [
                    $className,
                    $reflector,
                    $args,
                    $getters,
                    $setters,
                    $property,
                ];
            }
        }
    }

    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
    ) : Generator {
        foreach (
            $this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as [
                $className,
                $reflector,
                $args,
                $getters,
                $setters,
                $property,
            ]
        ) {
            if (
                false === in_array(
                    $property,
                    $className::DaftObjectNullableProperties(),
                    true
                )
            ) {
                yield [
                    $className,
                    $reflector,
                    $args,
                    $getters,
                    $setters,
                    $property,
                ];
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
        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));

        foreach ($properties as $property) {
            static::assertInternalType(
                'string',
                $property,
                (
                    $className .
                    '::DaftObjectProperties()' .
                    ' must return an array of strings'
                )
            );
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractDefinesOwnIdGoodImplementations
    */
    final public function testHasDefinedAllIdPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $interfaceCheck = $className;

        static::assertTrue(
            is_a(
                $interfaceCheck,
                DaftObject\DefinesOwnIdPropertiesInterface::class,
                true
            )
        );

        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));

        foreach ($className::DaftObjectIdProperties() as $property) {
            static::assertInternalType(
                'string',
                $property,
                (
                    $className .
                    '::DaftObjectIdProperties()' .
                    ' must return an array of strings'
                )
            );
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
        $nullables = $className::DaftObjectNullableProperties();

        foreach ($nullables as $nullable) {
            static::assertInternalType(
                'string',
                $nullable,
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' must return an array of strings'
                )
            );
        }

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
                $getter = 'Get' . $property;
                $setter = 'Set' . $property;

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
                    * @var \ReflectionType $returnType
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
        $exportables = $className::DaftObjectExportableProperties();

        foreach ($exportables as $exportable) {
            static::assertInternalType(
                'string',
                $exportable,
                (
                    $className .
                    '::DaftObjectExportableProperties()' .
                    ' must return an array of strings'
                )
            );
        }

        $properties = $className::DaftObjectProperties();

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
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodPropertiesImplementations
    */
    final public function testHasDefinedImplementationCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $properties = $className::DaftObjectProperties();

        $nullables = $className::DaftObjectNullableProperties();

        $exportables = $className::DaftObjectExportableProperties();

        /*
        * @var ReflectionClass $reflector
        */

        foreach ($properties as $property) {
            $getter = 'Get' . ucfirst($property);
            $setter = 'Set' . ucfirst($property);

            $hasAny = (
                $reflector->hasMethod($getter) ||
                $reflector->hasMethod($setter)
            );

            static::assertTrue(
                $hasAny,
                (
                    $className .
                    ' must implement at least a getter or setter for ' .
                    $className .
                    '::$' .
                    $property
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
                * @var ReflectionMethod $reflectorGetter
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
                * @var \ReflectionType $returnType
                */
                $returnType = $reflectorGetter->getReturnType();

                static::assertTrue(
                    (
                        'void' !== $returnType->__toString()
                    ),
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
                        (
                            $returnType->allowsNull()
                        ),
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
                * @var ReflectionMethod $reflectorSetter
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
                * @var \ReflectionType $returnType
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

                if (
                    $reflectorSetter->getParameters()[0]->hasType()
                ) {
                    static::assertSame(
                        (
                            $reflectorSetter->getParameters()[0]
                        )->getType()->allowsNull(),
                        $isNullable,
                        (
                            $reflectorSetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorSetter->getName() .
                            '() must have a ' .
                            (
                                $isNullable
                                    ? ''
                                    : 'non-'
                            ) .
                            'nullable type when specified.'
                        )
                    );
                }
            }
        }
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
        $property = mb_substr($reflector->getName(), 3);

        $properties = $className::DaftObjectProperties();

        $defined = (
            in_array($property, $properties, true) ||
            in_array(
                lcfirst($property),
                $properties,
                true
            )
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
    * @dataProvider dataProviderNonAbstractNonWormGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    *
    * @psalm-suppress ForbiddenCode
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlank(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $interfaceCheck = $className;

        $obj = new $className($args);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            (
                $className .
                '::ChangedProperties() must be empty after instantiation'
            )
        );

        $obj = new $className([]);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            (
                $className .
                '::ChangedProperties() must be empty after instantiation'
            )
        );

        $settersNotNull = [];

        foreach ($setters as $property) {
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

            if (isset($args[$property])) {
                $obj->$property = $args[$property];

                static::assertTrue(
                    $obj->HasPropertyChanged($property),
                    (
                        $className .
                        '::$' .
                        $property .
                        ' should be marked as changed.'
                    )
                );

                $obj->MakePropertiesUnchanged($property);

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
        * @var DaftObject\DaftObject $obj
        */
        $obj = new $className([]);

        foreach ($setters as $property) {
            $obj->$property = $args[$property];

            if (in_array($property, $getters, true)) {
                static::assertSame(
                    $args[$property],
                    $obj->$property
                );
            }
        }

        ob_start();
        var_dump($obj);

        $debugInfo = ob_get_clean();

        $regex = '/' . static::RegexForObject($obj) . '$/s';

        static::assertRegExp(
            $regex,
            str_replace(["\n"], ' ', (string) $debugInfo)
        );

        foreach ($setters as $property) {
            static::assertTrue(
                in_array($property, $obj->ChangedProperties(), true),
                (
                    $className .
                    '::ChangedProperties() must contain changed properties'
                )
            );
        }

        foreach ($className::DaftObjectNullableProperties() as $property) {
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
                    (
                        $className .
                        '::$' .
                        $property .
                        ' must be null after being unset'
                    )
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
        $obj = new $className($args);

        if (
            ($obj instanceof DaftObject\DaftJson)
        ) {
            $obj->jsonSerialize();

            $json = json_encode($obj);

            static::assertInternalType(
                'string',
                $json,
                (
                    'Instances of ' .
                    $className .
                    ' should resolve to a string when passed to json_encode()'
                )
            );

            $decoded = json_decode($json, true);

            static::assertInternalType(
                'array',
                $decoded,
                (
                    'JSON-encoded implementations of ' .
                    DaftObject\DaftJson::class .
                    ' (' .
                    $className .
                    ')' .
                    ' must decode to an array!'
                )
            );

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
                $this->expectExceptionMessage(
                    sprintf(
                        '%s does not implement %s',
                        $className,
                        DaftObject\DaftJson::class
                    )
                );

                $obj->jsonSerialize();

                return;
            }
            static::markTestSkipped(
                sprintf(
                    '%s does not implement %s or %s::jsonSerialize()',
                    $className,
                    DaftObject\DaftJson::class,
                    $className
                )
            );

            return;
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    *
    * @psalm-suppress TypeDoesNotContainType
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromArrayFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                '%s does not implement %s',
                $className,
                DaftObject\DaftJson::class
            )
        );

        /**
        * @var DaftObject\DaftJson $className
        */
        $className = $className;

        $className::DaftObjectFromJsonArray([]);
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
    *
    * @psalm-suppress TypeDoesNotContainType
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromStringFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                '%s does not implement %s',
                $className,
                DaftObject\DaftJson::class
            )
        );

        /**
        * @var DaftObject\DaftJson $className
        */
        $className = $className;

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
            $exportables = $className::DaftObjectExportableProperties();

            $propertyNames = $className::DaftObjectJsonPropertyNames();

            $jsonProps = [];

            foreach ($className::DaftObjectJsonProperties() as $k => $v) {
                $prop = $v;

                if (is_string($k)) {
                    static::assertInternalType(
                        'string',
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
        } elseif (
            is_a(
                $className,
                DaftObject\AbstractArrayBackedDaftObject::class,
                true
            )
        ) {
            $this->expectException(DaftObject\DaftObjectNotDaftJsonBadMethodCallException::class);
            $this->expectExceptionMessage(
                sprintf(
                    '%s does not implement %s',
                    $className,
                    DaftObject\DaftJson::class
                )
            );

            /**
            * @var DaftObject\DaftJson $className
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
        $obj = new $className($args, true);

        $this->expectException(
            DaftObject\PropertyNotRewriteableException::class
        );
        $this->expectExceptionMessage(
            sprintf(
                'Property not rewriteable: ' .
                $className .
                '::$' .
                $property
            )
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
        $obj = new $className([], true);

        $obj->$property = $args[$property];

        $this->expectException(
            DaftObject\PropertyNotRewriteableException::class
        );
        $this->expectExceptionMessage(
            sprintf(
                'Property not rewriteable: ' .
                $className .
                '::$' .
                $property
            )
        );

        $obj->$property = $args[$property];
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable
    *
    * @psalm-suppress TypeDoesNotContainType
    */
    final public function testNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        if (
            is_a($className, DaftObject\AbstractDaftObject::class, true) &&
            in_array($property, $setters, true)
        ) {
            /**
            * @var ReflectionMethod $method
            */
            $method = $reflector->getMethod('NudgePropertyValue');

            $method->setAccessible(true);

            $this->expectException(
                DaftObject\PropertyNotNullableException::class
            );
            $this->expectExceptionMessage(
                sprintf(
                    'Property not nullable: %s::$%s',
                    $className,
                    $property
                )
            );

            $method->invoke(new $className(), $property, null);
        }
    }

    protected static function RegexForObject(DaftObject\DaftObject $obj) : string
    {
        $props = [];

        foreach ($obj::DaftObjectExportableProperties() as $prop) {
            $expectedMethod = 'Get' . ucfirst($prop);
            if (
                $obj->__isset($prop) &&
                method_exists($obj, $expectedMethod) &&
                (
                    new ReflectionMethod($obj, $expectedMethod)
                )->isPublic()
            ) {
                $props[$prop] = $obj->$expectedMethod();
            }
        }

        return static::RegexForArray(get_class($obj), $props);
    }

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

            foreach ($val as $v) {
                $out .= static::RegexForVal($v);
            }

            $out .= ')';

            return $out;
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
                                    : (
                                        is_object($val)
                                            ? ''
                                    : preg_quote(gettype($val), '/')
                                    )
                            )
                    )
            ) .
            (
                ($val instanceof DaftObject\DaftObject)
                    ? (
                        '(?:' .
                            static::RegexForObject(
                                $val
                            ) .
                        ')'
                    )
                    : preg_quote(
                        (
                            '(' .
                            (
                                is_string($val)
                                    ? mb_strlen($val, '8bit')
                                    : (
                                        is_numeric($val)
                                            ? (string) $val
                                            : var_export($val, true)
                                    )
                            ) .
                            ')' .
                            (
                                is_string($val)
                                    ? (' "' . $val . '"')
                                    : ''
                            )
                        ),
                        '/'
                    )
        );
    }

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
        ];
    }

    protected function FuzzingImplementationsViaGenerator() : Generator
    {
        foreach (
            $this->FuzzingImplementationsViaArray() as $args
        ) {
            yield $args;
        }
    }
}
