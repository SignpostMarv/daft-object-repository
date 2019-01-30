<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTime;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\MultiTypedArrayPropertiesTester;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeParanoia;

class MultiTypedArrayPropertyImplementationTest extends TestCase
{
    public function DataProviderObjectPropertyValueException() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                0,
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    TypeParanoia::class .
                    '::MaybeThrowIfValueDoesNotMatchMultiTypedArray must be an array' .
                    ', integer given!'
                ),
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                ['foo' => 'bar'],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    TypeParanoia::class .
                    '::MaybeThrowIfNotArrayIntKeys must be array<int, mixed>'
                ),
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTime()],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    TypeParanoia::class .
                    '::MaybeThrowIfValueArrayDoesNotMatchTypes' .
                    ' contained values that did not match the provided types!'
                ),
            ],
        ];
    }

    public function DataProviderObjectPropertyValueNotUniqueAutoDouble() : Generator
    {
        /**
        * @var iterable<array<int, mixed>>
        */
        $sources = $this->DataProviderObjectPropertyValueNotUnique();

        foreach ($sources as $args) {
            static::assertIsArray($args[2] ?? null);
            $args[2] = array_merge(array_values((array) $args[2]), array_values((array) $args[2]));

            yield $args;
        }
    }

    public function DataProviderObjectPropertyValueTrimmedStrings() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'trimmedStrings',
                [' foo', 'foo', 'foo ', ' foo '],
                ['foo'],
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'trimmedString',
                ' foo ',
                'foo',
            ],
        ];
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @dataProvider DataProviderObjectPropertyValueException
    */
    public function testNudgingThrows(
        DaftObject $obj,
        string $property,
        $value,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        $obj->__set($property, $value);
    }

    /**
    * @dataProvider DataProviderObjectPropertyValueNotUniqueAutoDouble
    */
    public function testNonUniqueThrows(
        DaftObject $obj,
        string $property,
        array $value
    ) : void {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 3 passed to ' .
            TypeParanoia::class .
            '::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray contained non-unique values!'
        );

        $obj->__set($property, $value);
    }

    /**
    * @param scalar|array|object|null $value
    * @param scalar|array|object|null $expected
    *
    * @dataProvider DataProviderObjectPropertyValueTrimmedStrings
    */
    public function testAutoTrimmedStrings(
        DaftObject $obj,
        string $property,
        $value,
        $expected
    ) : void {
        $obj->__set($property, $value);

        static::assertSame($expected, $obj->__get($property));
    }

    protected function DataProviderObjectPropertyValueNotUnique() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTimeImmutable()],
            ],
        ];
    }
}
