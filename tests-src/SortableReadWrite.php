<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class SortableReadWrite extends ReadWrite implements DaftSortableObject
{
    const SORTABLE_PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
    ];
}
