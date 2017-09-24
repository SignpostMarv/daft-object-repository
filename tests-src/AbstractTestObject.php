<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractTestObject extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
    ];

    const NULLABLE_PROPERTIES = [
        'Bat',
    ];

    const EXPORTABLE_PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
    ];
}
