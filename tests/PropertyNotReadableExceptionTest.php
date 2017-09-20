<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftObject\PropertyNotReadableException;
use SignpostMarv\DaftObject\WriteOnly;

class PropertyNotReadableExceptionTest extends TestCase
{
    public function dataProviderPropertyNotReadableException() : array
    {
        return [
            [
                WriteOnly::class,
                [
                    'Foo' => 'bar',
                ],
                'Foo',
            ],
        ];
    }

    /**
    * @dataProvider dataProviderPropertyNotReadableException
    */
    public function testPropertyNotReadableException(
        string $implementation,
        array $args,
        string $propertyToGet
    ) : void {
        $this->expectException(PropertyNotReadableException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property not readable: %s::$%s',
                $implementation,
                $propertyToGet
            )
        );

        $obj = new $implementation($args);

        $foo = $obj->$propertyToGet;
    }
}
