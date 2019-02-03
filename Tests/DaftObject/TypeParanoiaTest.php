<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use InvalidArgumentException;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeParanoia;

class TypeParanoiaTest extends TestCase
{
    public function DataProviderThrowIfNotType() : array
    {
        return [
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
    * @param string|object $object
    * @param string[] $types
    *
    * @psalm-param class-string|object $object
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
}
