<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Base daft object.
*/
interface DaftObject
{
    /**
    * Maps param $property to the getter method.
    *
    * @param string $property the property being retrieved
    *
    * @throws UndefinedPropertyException if a property is undefined
    *
    * @return mixed
    */
    public function __get(string $property);

    /**
    * Maps param $property to the getter method.
    *
    * @param string $property the property being retrieved
    * @param mixed $v
    *
    * @throws UndefinedPropertyException if a property is undefined
    *
    * @return mixed
    */
    public function __set(string $property, $v);

    /**
    * required to support isset($foo->bar);.
    *
    * @param string $property the property being checked
    */
    public function __isset(string $property) : bool;

    /**
    * required to support unset($foo->bar).
    *
    * @param string $property the property being unset
    *
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void;

    /**
    * Get the changed properties on an object.
    *
    * @return string[]
    */
    public function ChangedProperties() : array;

    /**
    * Mark the specified properties as unchanged.
    *
    * @param string ...$properties the property being set as unchanged
    */
    public function MakePropertiesUnchanged(
        string ...$properties
    ) : void;

    /**
    * Check if a property exists on an object.
    *
    * @param string $property the property being checked
    *
    * @return bool
    */
    public function HasPropertyChanged(string $property) : bool;

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return string[]
    */
    public static function DaftObjectProperties() : array;

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @return string[]
    */
    public static function DaftObjectNullableProperties() : array;
}
