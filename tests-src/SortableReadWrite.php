<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as SortableReadWrite
*
* @template-implements DaftSortableObject<T>
*/
class SortableReadWrite extends ReadWrite implements DaftSortableObject
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
