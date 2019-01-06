<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use DateTimeImmutable;
use DateTimeInterface;
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
}
