<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableException;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableShouldBeArrayException;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\ReadWriteJson;
use SignpostMarv\DaftObject\ReadWriteJsonJson;
use SignpostMarv\DaftObject\ReadWriteJsonJsonArray;
use SignpostMarv\DaftObject\ReadWriteJsonJsonArrayBad;

class DaftJsonExceptionTest extends TestCase
{
    public function dataProviderClassDoesNotImplementClassException() : array
    {
        return [
            [
                ReadWriteJsonJsonArrayBad::class,
                'stdClass',
                [
                    'json' => [
                        [
                            'Foo' => 'Foo',
                            'Bar' => 1.0,
                            'Baz' => 2,
                            'Bat' => true,
                        ],
                    ],
                ],
                true,
            ],
        ];
    }

    public function dataProviderPropertyNotThingableException() : array
    {
        return [
            [
                ReadWriteJsonJsonArrayBad::class,
                ReadWriteJsonJsonArrayBad::class,
                'json',
                PropertyNotNullableException::class,
                'nullable',
                [
                    'json' => null,
                ],
                true,
            ],
            [
                ReadWriteJsonJsonArray::class,
                ReadWriteJsonJsonArray::class,
                'notthere',
                PropertyNotJsonDecodableException::class,
                'json-decodable',
                [
                    'json' => [],
                    'notthere' => 1,
                ],
                true,
            ],
            [
                ReadWriteJsonJsonArray::class,
                ReadWriteJsonJsonArray::class,
                'json',
                PropertyNotJsonDecodableShouldBeArrayException::class,
                'json-decodable (should be an array)',
                [
                    'json' => 1,
                ],
                true,
            ],
            [
                ReadWriteJsonJson::class,
                ReadWriteJson::class,
                'json',
                PropertyNotJsonDecodableShouldBeArrayException::class,
                'json-decodable (should be an array)',
                [
                    'json' => 1,
                ],
                true,
            ],
            [
                ReadWriteJsonJsonArray::class,
                ReadWriteJson::class,
                'json',
                PropertyNotJsonDecodableShouldBeArrayException::class,
                'json-decodable (should be an array)',
                [
                    'json' => [
                        1,
                    ],
                ],
                true,
            ],
        ];
    }

    /**
    * @dataProvider dataProviderClassDoesNotImplementClassException
    */
    public function testClassDoesNotImplementClassException(
        string $implementation,
        string $expectingFailureWith,
        array $args,
        bool $writeAll
    ) : void {
        if ( ! is_subclass_of($implementation, AbstractArrayBackedDaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                AbstractArrayBackedDaftObject::class
            );

            return;
        }

        $this->expectException(ClassDoesNotImplementClassException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $expectingFailureWith,
            DaftJson::class
        ));

        $implementation::DaftObjectFromJsonArray($args, $writeAll);
    }

    /**
    * @dataProvider dataProviderPropertyNotThingableException
    */
    public function testPropertyNotThingableException(
        string $implementation,
        string $expectingFailureWithClass,
        string $expectingFailureWithProperty,
        string $expectingException,
        string $expectingThing,
        array $args,
        bool $writeAll
    ) : void {
        if ( ! is_subclass_of($implementation, DaftJson::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftJson::class
            );

            return;
        }

        $this->expectException($expectingException);
        $this->expectExceptionMessage(sprintf(
            'Property not %s: %s::$%s',
            $expectingThing,
            $expectingFailureWithClass,
            $expectingFailureWithProperty
        ));

        /**
        * @var DaftJson
        */
        $obj = $implementation::DaftObjectFromJsonArray($args, $writeAll);

        /**
        * @var array<int, string>
        */
        $args = $implementation::DaftObjectPublicGetters();

        foreach ($args as $arg) {
            $obj->$arg;
        }
    }
}
