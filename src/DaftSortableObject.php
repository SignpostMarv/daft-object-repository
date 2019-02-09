<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftSortableObject
*/
interface DaftSortableObject extends DaftObject
{
    /**
    * @psalm-param T $otherObject
    */
    public function CompareToDaftSortableObject(DaftSortableObject $otherObject) : int;

    /**
    * @return array<int, string>
    */
    public static function DaftSortableObjectProperties() : array;
}
