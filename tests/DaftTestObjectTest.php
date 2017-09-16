<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\DefinesOwnStringIdInterface;
use SignpostMarv\DaftObject\DefinesOwnUntypedIdInterface;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\PropertyNotWriteableException;
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
use TypeError;

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
                ReadOnly::class,
                UndefinedPropertyException::class,
                (
                    'Undefined property: ' .
                    ReadOnly::class .
                    '::$NotFoo'
                ),
                [
                    'NotFoo' => 1,
                ],
                true,
                false,
            ],
            [
                WriteOnly::class,
                PropertyNotWriteableException::class,
                (
                    'Property not writeable: ' .
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
                WriteOnly::class,
                PropertyNotNullableException::class,
                (
                    'Property not nullable: ' .
                    WriteOnly::class .
                    '::$Foo'
                ),
                [
                    'FooNotNullable' => null,
                ],
                false,
                true,
            ],
            [
                WriteOnly::class,
                UndefinedPropertyException::class,
                (
                    'Undefined property: ' .
                    WriteOnly::class .
                    '::$BarUndefined'
                ),
                [
                    'BarUndefined' => 1,
                ],
                false,
                true,
            ],
            [
                ReadOnlyBad::class,
                TypeError::class,
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
                ReadOnlyBad::class,
                TypeError::class,
                (
                    ReadOnlyBad::class .
                    ' already determined to be incorrectly implemented'
                ),
                [
                    'Foo' => 'Bar',
                ],
                true,
                false,
            ],
            [
                ReadOnlyBadDefinesOwnId::class,
                TypeError::class,
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
                TypeError::class,
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

        foreach ($this->GoodDataProvider() as $args) {
            if (
                is_a($args[0], DefinesOwnUntypedIdInterface::class, true) &&
                $args[2] === true
            ) {
                $out[] = [$args[0], $args[1]];
            }
        }

        return $out;
    }

    /**
    * @dataProvider GoodDataProvider
    */
    public function testGood(
        string $implementation,
        array $params,
        bool $readable = false,
        bool $writeable = false
    ) : void {
        $obj = new $implementation($params, $writeable);

        /**
        * @var DaftObject $implementation
        */
        $implementation = $implementation;

        if ($readable === true) {
            $this->assertSame(
                ($writeable ? count($params) : 0),
                count($obj->ChangedProperties())
            );

            foreach ($params as $k => $v) {
                $getterMethod = 'Get' . ucfirst($k);

                $this->assertSame(
                    $params[$k],
                    $obj->$getterMethod(),
                    (
                        (string) $implementation .
                        '::' .
                        $getterMethod .
                        '() does not match supplied $params'
                    )
                );
                $this->assertSame(
                    $params[$k],
                    $obj->$k,
                    (
                        (string) $implementation .
                        '::$' .
                        $k .
                        ' does not match supplied $params'
                    )
                );

                $this->assertSame(
                    (is_null($params[$k]) ? false : true),
                    isset($obj->$k),
                    (
                        (string) $implementation .
                        '::$' .
                        $k .
                        ' was not found as ' .
                        (is_null($params[$k]) ? 'not set' : 'set')
                    )
                );
            }
        }

        foreach (array_keys($params) as $property) {
            $this->assertSame(
                $writeable,
                $obj->HasPropertyChanged($property),
                (
                    (string) $implementation .
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
                $this->assertSame(false, $obj->HasPropertyChanged($property));

                if (
                    in_array(
                        $property,
                        $implementation::DaftObjectNullableProperties(),
                        true
                    )
                ) {
                    if ($obj instanceof DaftObjectWorm) {
                        $this->expectException(
                            PropertyNotRewriteableException::class
                        );
                        $this->expectExceptionMessage(
                            sprintf(
                                'Property not rewriteable: %s::$%s',
                                $implementation,
                                $property
                            )
                        );
                    }
                    unset($obj->$property);
                    $obj->$property = null;
                }
            }
        }

        $obj->MakePropertiesUnchanged(...array_keys($params));
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
        if ($readable) {
            $this->expectException($expectedExceptionType);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $obj = new $implementation($params, $writeable);

            foreach (array_keys($params) as $property) {
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
    */
    public function testDefinesOwnUntypedIdInterface(
        string $implementation,
        array $params
    ) : void {
        $obj = new $implementation($params, false);
        $val = $obj->id;

        /**
        * @var DefinesOwnIdPropertiesInterface $implementation
        */
        $implementation = $implementation;

        $keys = $implementation::DaftObjectIdProperties();

        if (count($keys) < 2) {
            $key = $keys[0];
            $this->assertSame($val, $obj->$key);
        } else {
            $this->assertInternalType('array', $val);
            $keyVals = [];
            foreach ($keys as $i => $key) {
                $this->assertSame($val[$i], $obj->$key);
            }
        }

        if ($obj instanceof DefinesOwnStringIdInterface) {
            $this->assertInternalType('string', $val);
        } elseif ($obj instanceof DefinesOwnIntegerIdInterface) {
            $this->assertInternalType('int', $val);
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
        $obj = new $implementation();

        /**
        * @var DaftObject $implementation
        */
        $implementation = $implementation;

        $props = $implementation::DaftObjectProperties();
        $nullables = $implementation::DaftObjectNullableProperties();

        $allNullable = true;

        foreach ($props as $prop) {
            if (in_array($prop, $nullables, true) === false) {
                $allNullable = false;
                break;
            }
        }

        if ($allNullable) {
            $this->markTestSkipped(
                'Cannot test for not nullable exception if all properties are nullable'
            );
        }

        $prop = $props[0];

        $this->expectException(PropertyNotNullableException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property not nullable: %s::$%s',
                $implementation,
                $prop
            )
        );

        $obj->$prop;
    }
}
