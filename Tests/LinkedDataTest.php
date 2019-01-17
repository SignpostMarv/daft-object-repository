<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\LinkedData\HasId;

class LinkedDataTest extends TestCase
{
    public function testJsonEncode() : void
    {
        $foo = new HasId(['@id' => 'foo']);

        static::assertSame('{"@id":"foo"}', json_encode($foo));

        $bar = HasId::DaftObjectFromJsonString('{"@id":"foo"}');

        static::assertInstanceOf(HasId::class, $bar);
        static::assertSame('{"@id":"foo"}', json_encode($bar));

        $bar->__set('@id', 'bar');
        static::assertSame('{"@id":"bar"}', json_encode($bar));
    }
}
