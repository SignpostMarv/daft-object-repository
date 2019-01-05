<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues extends DaftObject
{
    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues() : array;
}
