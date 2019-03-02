<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DaftObject;

use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeUtilities;

class TypeUtilitiesTest extends TestCase
{
    /**
    * @psalm-param class-string<AbstractDaftObject> $className
    *
    * @dataProvider dataProvider_AbstractDaftObject__has_properties
    */
    public function test_DaftObjectPublicOrProtectedGetters(string $className) : void
    {
        $expected = count((array) $className::PROPERTIES);

        if (is_a($className, DefinesOwnIdPropertiesInterface::class, true)) {
            ++$expected;
        }

        static::assertLessThanOrEqual(
            $expected,
            count(TypeUtilities::DaftObjectPublicOrProtectedGetters($className))
        );
    }
}
