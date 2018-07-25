<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftSortableObject extends DaftObject
{
    public function CompareToDaftSortableObject(DaftSortableObject $otherObject) : int;

    /**
    * @return array<int, string>
    */
    public static function DaftSortableObjectProperties() : array;
}
