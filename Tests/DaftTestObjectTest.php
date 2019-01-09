<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use ReflectionMethod;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\ClassMethodReturnHasZeroArrayCountException;
use SignpostMarv\DaftObject\ClassMethodReturnIsNotArrayOfStringsException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\DefinesOwnStringIdInterface;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\PropertyNotRewriteableException;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\ReadOnlyBad;
use SignpostMarv\DaftObject\ReadOnlyBadDefinesOwnId;
use SignpostMarv\DaftObject\ReadOnlyInsuficientIdProperties;
use SignpostMarv\DaftObject\ReadOnlyTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\ReadWriteWorm;
use SignpostMarv\DaftObject\UndefinedPropertyException;
use SignpostMarv\DaftObject\WriteOnly;
use SignpostMarv\DaftObject\WriteOnlyWorm;

class DaftTestObjectTest extends TestCase
{
    public function GoodDataProvider() : array
    {
        return [
            [
                ReadOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                true,
                false,
            ],
            [
                ReadOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                true,
                false,
            ],
            [
                ReadOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                false,
            ],
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                true,
                false,
            ],
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                true,
                false,
            ],
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                false,
            ],
            [
                ReadWriteTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                true,
                false,
            ],
            [
                ReadWriteTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                true,
                false,
            ],
            [
                ReadWriteTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                false,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                false,
                true,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                false,
                true,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                false,
                true,
            ],
            [
                ReadWrite::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                true,
                true,
            ],
            [
                ReadWrite::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                true,
                true,
            ],
            [
                ReadWrite::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                true,
            ],
            [
                ReadWriteWorm::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                true,
            ],
            [
                WriteOnlyWorm::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                false,
                true,
            ],
        ];
    }

    public function ThrowsExceptionProvider() : array
    {
        return [
            [
                WriteOnly::class,
                UndefinedPropertyException::class,
                (
                    'Property not defined: ' .
                    WriteOnly::class .
                    '::$NotFoo'
                ),
                [
                    'NotFoo' => 1,
                ],
                false,
                true,
            ],
            [
                ReadOnlyBad::class,
                ClassMethodReturnIsNotArrayOfStringsException::class,
                (
                    ReadOnlyBad::class .
                    '::DaftObjectIdProperties() does not return string[]'
                ),
                [
                    'Foo' => 'Bar',
                ],
                true,
                false,
            ],
            [
                ReadOnlyBadDefinesOwnId::class,
                ClassDoesNotImplementClassException::class,
                (
                    ReadOnlyBadDefinesOwnId::class .
                    ' does not implement ' .
                    DefinesOwnIdPropertiesInterface::class
                ),
                [
                    'Foo' => 'Bar',
                ],
                true,
                false,
            ],
            [
                ReadOnlyInsuficientIdProperties::class,
                ClassMethodReturnHasZeroArrayCountException::class,
                (
                    ReadOnlyInsuficientIdProperties::class .
                    '::DaftObjectIdProperties() must return at least one property'
                ),
                [
                    'Foo' => 'Bar',
                ],
                true,
                false,
            ],
        ];
    }

    public function DefinesOwnUntypedIdInterfaceProvider() : array
    {
        $out = [];

        /**
        * @var \Traversable<array<int, string|bool|array<string, scalar>>>
        */
        $implementations = $this->GoodDataProvider();

        foreach ($implementations as $args) {
            if (
                is_string($args[0]) &&
                ! is_a($args[0], DefinesOwnArrayIdInterface::class, true) &&
                ! is_a($args[0], DefinesOwnStringIdInterface::class, true) &&
                ! is_a($args[0], DefinesOwnIntegerIdInterface::class, true) &&
                true === $args[2]
            ) {
                $out[] = [$args[0], $args[1]];
            }
        }

        return $out;
    }

    /**
    * @param array<string, scalar|array|object|null> $params
    *
    * @dataProvider GoodDataProvider
    */
    public function testGood(
        string $implementation,
        array $params,
        bool $readable = false,
        bool $writeable = false
    ) : void {
        if ( ! is_subclass_of($implementation, DaftObject::class, true)) {
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
        $obj = new $implementation($params, $writeable);

        if (true === $readable) {
            static::assertCount(($writeable ? count($params) : 0), $obj->ChangedProperties());

            foreach ($params as $k => $v) {
                $getterMethod = static::MethodNameFromProperty($k);

                static::assertSame(
                    $params[$k],
                    $obj->$getterMethod(),
                    (
                        $implementation .
                        '::' .
                        $getterMethod .
                        '() does not match supplied $params'
                    )
                );
                static::assertSame(
                    $params[$k],
                    $obj->$k,
                    (
                        $implementation .
                        '::$' .
                        $k .
                        ' does not match supplied $params'
                    )
                );

                static::assertSame(
                    (is_null($params[$k]) ? false : true),
                    isset($obj->$k),
                    (
                        $implementation .
                        '::$' .
                        $k .
                        ' was not found as ' .
                        (is_null($params[$k]) ? 'not set' : 'set')
                    )
                );
            }
        }

        foreach (array_keys($params) as $property) {
            static::assertSame(
                $writeable,
                $obj->HasPropertyChanged($property),
                (
                    $implementation .
                    '::$' .
                    $property .
                    ' was' .
                    ($writeable ? ' ' : ' not ') .
                    'writeable, property should' .
                    ($writeable ? ' ' : ' not ') .
                    'be changed'
                )
            );

            if ($writeable) {
                $obj->MakePropertiesUnchanged($property);
                static::assertFalse($obj->HasPropertyChanged($property));

                if (
                    in_array(
                        $property,
                        (array) $implementation::DaftObjectNullableProperties(),
                        true
                    )
                ) {
                    if ($obj instanceof DaftObjectWorm) {
                        $this->expectException(PropertyNotRewriteableException::class);
                        $this->expectExceptionMessage(sprintf(
                            'Property not rewriteable: %s::$%s',
                            $implementation,
                            $property
                        ));
                    }
                    unset($obj->$property);
                    $obj->$property = null;
                }
            }
        }

        $obj->MakePropertiesUnchanged(...array_keys($params));

        $debugInfo = $this->VarDumpDaftObject($obj);

        /**
        * @var array<string, scalar|array|object|bool|float|null>
        */
        $props = [];

        foreach ($obj::DaftObjectExportableProperties() as $prop) {
            $expectedMethod = static::MethodNameFromProperty($prop);
            if (
                $obj->__isset($prop) &&
                method_exists($obj, $expectedMethod) &&
                (
                    new ReflectionMethod($obj, $expectedMethod)
                )->isPublic()
            ) {
                /**
                * @var scalar|array|object|null
                */
                $propVal = $obj->$expectedMethod();

                $props[$prop] = $propVal;
            }
        }

        $regex =
            '/(?:class |object\()' .
            preg_quote(get_class($obj), '/') .
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
                (
                    is_int($val)
                        ? 'int'
                        : (
                            is_bool($val)
                                ? 'bool'
                                : (
                                    is_float($val)
                                        ? '(?:float|double)'
                                        : preg_quote(gettype($val), '/')
                                )
                        )
                ) .
                preg_quote(
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
                ) .
                '.+';
        }

        $regex .= '\}.+$/s';

        static::assertRegExp($regex, str_replace("\n", ' ', $debugInfo));
    }

    /**
    * @dataProvider ThrowsExceptionProvider
    */
    public function testThrowsException(
        string $implementation,
        string $expectedExceptionType,
        string $expectedExceptionMessage,
        array $params,
        bool $readable,
        bool $writeable
    ) : void {
        if ( ! is_subclass_of($implementation, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        if ($readable) {
            $this->expectException($expectedExceptionType);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $obj = new $implementation($params, $writeable);

            /**
            * @var array<int, int|string>
            */
            $paramKeys = array_keys($params);

            foreach ($paramKeys as $property) {
                /**
                * @var scalar|array|object|null
                */
                $var = $obj->$property;
            }
        } elseif ($writeable) {
            $this->expectException($expectedExceptionType);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $obj = new $implementation($params, $writeable);
        }
    }

    /**
    * @dataProvider DefinesOwnUntypedIdInterfaceProvider
    *
    * @psalm-suppress NoInterfaceProperties
    */
    public function testDefinesOwnUntypedIdInterface(string $implementation, array $params) : void
    {
        if ( ! is_subclass_of($implementation, DefinesOwnIdPropertiesInterface::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DefinesOwnIdPropertiesInterface::class
            );

            return;
        }

        $obj = new $implementation($params, false);

        /**
        * @var array<int, scalar|array|object|null>|null
        */
        $val = $obj->id;

        /**
        * @var array<int, string>
        */
        $keys = $implementation::DaftObjectIdProperties();

        if (count($keys) < self::MIN_EXPECTED_ARRAY_COUNT) {
            $key = $keys[0];
            static::assertSame($val, $obj->$key);
        } else {
            static::assertIsArray($val);

            /**
            * @var array<int, scalar|array|object|null>
            */
            $val = $val;

            $keyVals = [];
            foreach ($keys as $i => $key) {
                static::assertSame($val[$i], $obj->$key);
            }
        }

        if ($obj instanceof DefinesOwnStringIdInterface) {
            /**
            * @var scalar|null
            */
            $val = $val;

            static::assertIsString($val);
        } elseif ($obj instanceof DefinesOwnIntegerIdInterface) {
            /**
            * @var scalar|null
            */
            $val = $val;

            static::assertIsInt($val);
        }
    }

    public function RetrievePropertyValueFromDataNotNullableExceptionDataProvider() : array
    {
        return [
            [
                ReadOnly::class,
            ],
        ];
    }

    /**
    * @dataProvider RetrievePropertyValueFromDataNotNullableExceptionDataProvider
    */
    public function testRetrievePropertyValueFromDataNotNullableException(
        string $implementation
    ) : void {
        if ( ! is_subclass_of($implementation, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        $obj = new $implementation();

        /**
        * @var array<int, string>
        */
        $props = $implementation::DaftObjectProperties();

        /**
        * @var array<int, string>
        */
        $nullables = $implementation::DaftObjectNullableProperties();

        $allNullable = true;

        foreach ($props as $prop) {
            if (false === in_array($prop, $nullables, true)) {
                $allNullable = false;
                break;
            }
        }

        if ($allNullable) {
            static::markTestSkipped(
                'Cannot test for not nullable exception if all properties are nullable'
            );
        }

        $prop = $props[0];

        $this->expectException(PropertyNotNullableException::class);
        $this->expectExceptionMessage(sprintf(
            'Property not nullable: %s::$%s',
            $implementation,
            $prop
        ));

        $obj->$prop;
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
}
