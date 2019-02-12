<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as ReadWriteJsonBad
*
* @template-implements DaftJson<T>
*/
class ReadWriteJsonBad extends ReadWrite implements DaftJson
{
    const PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
        'foo',
    ];

    const NULLABLE_PROPERTIES = [
        'Bat',
        'bat',
    ];

    const EXPORTABLE_PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
        'foo',
    ];
}
