<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use SignpostMarv\DaftObject\TypeParanoia;

class TypeParanoiaTest extends TestCase
{
    public function testIsSubThingStrings() : void
    {
        static::assertTrue(TypeParanoia::IsSubThingStrings(
            DateTimeImmutable::class,
            DateTimeInterface::class
        ));
        static::assertFalse(TypeParanoia::IsSubThingStrings(
            static::class,
            DateTimeImmutable::class
        ));
    }

    public function DataProviderEnsureArgumentIsArray() : array
    {
        return [
            [
                1,
                2,
                'foo',
            ],
        ];
    }

    /**
    * @dataProvider DataProviderEnsureArgumentIsArray
    *
    * @param mixed $maybe
    */
    public function testEnsureArgumentIsArrayFails(
        $maybe,
        ? int $argument,
        string $method
    ) : void {
        if (is_array($maybe)) {
            static::assertIsArray(TypeParanoia::EnsureArgumentIsArray($maybe, $argument, $method));
        } else {
            static::expectException(InvalidArgumentException::class);
            static::expectExceptionMessage(
                'Argument ' .
                (string) (is_null($argument) ? 1 : $argument) .
                ' passed to ' .
                $method .
                ' must be an array, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe)) .
                ' given!'
            );
            TypeParanoia::EnsureArgumentIsArray($maybe, $argument, $method);
        }
    }

    public function DataProviderEnsureArgumentIsString() : array
    {
        return [
            [
                'foo',
            ],
            [
                1,
            ],
            [
                new DateTimeImmutable(),
            ],
        ];
    }

    /**
    * @dataProvider DataProviderEnsureArgumentIsString
    *
    * @param mixed $maybe
    */
    public function testEnsureArgumentIsString($maybe) : void
    {
        if (is_string($maybe)) {
            static::assertSame($maybe, TypeParanoia::EnsureArgumentIsString($maybe));
        } else {
            static::expectException(InvalidArgumentException::class);
            static::expectExceptionMessage(
                'Argument 1 passed to ' .
                TypeParanoia::class .
                '::EnsureArgumentIsString must be a string, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe)) .
                ' given!'
            );

            TypeParanoia::EnsureArgumentIsString($maybe);
        }
    }
}
