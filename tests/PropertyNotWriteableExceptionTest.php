<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftObject\PropertyNotWriteableException;
use SignpostMarv\DaftObject\ReadOnly;

class PropertyNotWriteableExceptionTest extends TestCase
{
    public function dataProviderPropertyNotWriteableException() : array
    {
        return [
            [
                ReadOnly::class,
                'Foo',
            ],
        ];
    }

    /**
    * @dataProvider dataProviderPropertyNotWriteableException
    */
    public function testPropertyNotWriteableException(
        string $implementation,
        string $property
    ) : void {
        $this->expectException(PropertyNotWriteableException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property not writeable: %s::$%s',
                $implementation,
                $property
            )
        );

        $obj = new $implementation([
            $property => 'foo',
        ]);

        $obj->$property = 'foo';
    }
}
