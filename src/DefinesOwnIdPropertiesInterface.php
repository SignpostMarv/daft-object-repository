<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Interface for allowing daft object implementations to define their own ids.
*/
interface DefinesOwnIdPropertiesInterface extends DaftObject
{
    /**
    * Returns the property used in defining the unique id of an object.
    *
    * @return string[]
    */
    public static function DaftObjectIdProperties() : array;
}
