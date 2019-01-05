<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\NudgesIncorrectly;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\UndefinedPropertyException;
use SignpostMarv\DaftObject\WriteOnly;

class UndefinedPropertyExceptionTest extends TestCase
{
    public function dataProviderUndefinedPropertyException() : array
    {
        return [
            [
                ReadOnly::class,
                [
                    'nope' => 'foo',
                ],
                true,
                false,
                'nope',
            ],
            [
                WriteOnly::class,
                [
                    'nope' => 'foo',
                ],
                false,
                false,
                'nope',
            ],
            [
                NudgesIncorrectly::class,
                [
                    'Foo' => 'bar',
                ],
                true,
                true,
                'nope',
            ],
        ];
    }

    /**
    * @dataProvider dataProviderUndefinedPropertyException
    *
    * @psalm-suppress InvalidStringClass
    */
    public function testUndefinedPropertyException(
        string $implementation,
        array $args,
        bool $getNotSet,
        bool $writeAll,
        string $property
    ) : void {
        static::assertTrue(is_subclass_of($implementation, DaftObject::class, true));

        $this->expectException(UndefinedPropertyException::class);
        $this->expectExceptionMessage(sprintf(
            'Property not defined: %s::$%s',
            $implementation,
            $property
        ));

        $obj = new $implementation($args, $writeAll);

        if ($getNotSet) {
            /**
            * @var array|scalar|object|null
            */
            $foo = $obj->$property;
        } else {
            $obj->$property = 1;
        }
    }
}
