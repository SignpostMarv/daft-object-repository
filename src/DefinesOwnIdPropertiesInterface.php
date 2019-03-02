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
*
* @property-read scalar|scalar[] $id
*/
interface DefinesOwnIdPropertiesInterface extends DaftObject
{
    /**
    * Get the value of the Id.
    *
    * @return scalar|scalar[]
    */
    public function GetId();

    /**
    * Get the hash of the id properties.
    */
    public static function DaftObjectIdHash(self $object) : string;

    /**
    * Get the hash of the id properties.
    *
    * @param (scalar|array|object|null)[] $id
    */
    public static function DaftObjectIdValuesHash(array $id) : string;

    /**
    * Returns the property used in defining the unique id of an object.
    *
    * @return string[]
    */
    public static function DaftObjectIdProperties() : array;
}
