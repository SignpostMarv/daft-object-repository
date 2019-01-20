<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeByClassMethodAndTypeException;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeCertainty;
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
            static::assertSame(
                $maybe,
                TypeParanoia::EnsureArgumentIsArray($maybe, $argument, $method)
            );
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
                TypeCertainty::class .
                '::EnsureArgumentIsString must be a string, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe)) .
                ' given!'
            );

            TypeParanoia::EnsureArgumentIsString($maybe);
        }
    }

    public function DataProviderThrowIfNotType() : array
    {
        return [
            [
                1,
                5,
                TypeParanoia::class,
                'ThrowIfNotType',
                [
                    DaftObject::class,
                ],
                InvalidArgumentException::class,
                (
                    'Argument 1 passed to ' .
                    TypeParanoia::class .
                    '::ThrowIfNotType must be an object or a string!'
                ),
            ],
            [
                'foo',
                5,
                TypeParanoia::class,
                'ThrowIfNotType',
                [
                    'bar',
                ],
                InvalidArgumentException::class,
                (
                    'Argument 5 passed to ' .
                    TypeParanoia::class .
                    '::ThrowIfNotType must be a class or interface!'
                ),
            ],
        ];
    }

    /**
    * @param mixed $object
    * @param string[] $types
    *
    * @dataProvider DataProviderThrowIfNotType
    */
    public function testThrowIfNotType(
        $object,
        int $argument,
        string $class,
        string $function,
        array $types,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        TypeParanoia::ThrowIfNotType(
            $object,
            $argument,
            $class,
            $function,
            ...array_values($types)
        );
    }

    public function DataProviderThrowIfNotDaftObjectType() : array
    {
        return [
            [
                'stdClass',
                1,
                TypeParanoia::class,
                'ThrowIfNotType',
                [
                    DateTimeImmutable::class,
                ],
                DaftObjectRepositoryTypeByClassMethodAndTypeException::class,
                (
                    'Argument 1 passed to ' .
                    TypeParanoia::class .
                    '::ThrowIfNotType() must be an implementation of ' .
                    DaftObject::class .
                    ', stdClass given.'
                ),
            ],
        ];
    }

    /**
    * @param mixed $object
    * @param string[] $types
    *
    * @dataProvider DataProviderThrowIfNotDaftObjectType
    */
    public function testThrowIfNotDaftObjectType(
        $object,
        int $argument,
        string $class,
        string $function,
        array $types,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        TypeParanoia::ThrowIfNotDaftObjectType(
            $object,
            $argument,
            $class,
            $function,
            ...array_values($types)
        );
    }
}
