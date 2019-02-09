<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as SortableReadWrite
*/
class NotSortableReadWrite extends ReadWrite
{
    /**
    * @use TraitSortableDaftObject<T>
    */
    use TraitSortableDaftObject;

    const SORTABLE_PROPERTIES = [
        'Foo',
        'Bar',
        'Baz',
        'Bat',
    ];
}
