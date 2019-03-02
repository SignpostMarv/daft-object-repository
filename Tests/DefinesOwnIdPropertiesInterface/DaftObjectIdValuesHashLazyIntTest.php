<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DefinesOwnIdPropertiesInterface;

use PHPUnit\Framework\TestCase;

class DaftObjectIdValuesHashLazyIntTest extends TestCase
{
    public function test_DaftObjectIdHash() : void
    {
        $a = new Fixtures\ReadOnlyTwoColumnPrimaryKey(['Foo' => 'bar', 'Bar' => 1.2]);
        $b = new Fixtures\ReadOnlyTwoColumnPrimaryKey(['Foo' => 'bar', 'Bar' => 1.2]);

        static::assertSame(
            Fixtures\ReadOnlyTwoColumnPrimaryKey::DaftObjectIdHash($a),
            Fixtures\ReadOnlyTwoColumnPrimaryKey::DaftObjectIdHash($b)
        );
    }
}
