<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use DateTime;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use SignpostMarv\DaftObject;

class MultiTypedArrayPropertyImplementationTest extends TestCase
{
    public function DataProviderObjectPropertyValueException() : Generator
    {
        yield from [
            [
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'dates',
                0,
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DaftObject\TypeParanoia::class .
                    '::MaybeThrowIfValueDoesNotMatchMultiTypedArray must be an array' .
                    ', integer given!'
                ),
            ],
            [
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'dates',
                ['foo' => 'bar'],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DaftObject\TypeParanoia::class .
                    '::MaybeThrowIfNotArrayIntKeys must be array<int, mixed>'
                ),
            ],
            [
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTime()],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DaftObject\TypeParanoia::class .
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
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'trimmedStrings',
                [' foo', 'foo', 'foo ', ' foo '],
                ['foo'],
            ],
            [
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'trimmedString',
                ' foo ',
                'foo',
            ],
        ];
    }

    /**
    * @param mixed $value
    *
    * @dataProvider DataProviderObjectPropertyValueException
    */
    public function testNudgingThrows(
        DaftObject\DaftObject $obj,
        string $property,
        $value,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        $obj->$property = $value;
    }

    /**
    * @dataProvider DataProviderObjectPropertyValueNotUniqueAutoDouble
    */
    public function testNonUniqueThrows(
        DaftObject\DaftObject $obj,
        string $property,
        array $value
    ) : void {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 3 passed to ' .
            DaftObject\TypeParanoia::class .
            '::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray contained non-unique values!'
        );

        $obj->$property = $value;
    }

    /**
    * @param mixed $value
    * @param mixed $expected
    *
    * @dataProvider DataProviderObjectPropertyValueTrimmedStrings
    */
    public function testAutoTrimmedStrings(
        DaftObject\DaftObject $obj,
        string $property,
        $value,
        $expected
    ) : void {
        $obj->$property = $value;

        static::assertSame($expected, $obj->$property);
    }

    protected function DataProviderObjectPropertyValueNotUnique() : Generator
    {
        yield from [
            [
                new DaftObject\MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTimeImmutable()],
            ],
        ];
    }
}
