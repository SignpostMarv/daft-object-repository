<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractDaftObject
{
    /**
    * List of properties that can be defined on an implementation.
    *
    * @var string[]
    */
    const PROPERTIES = [];

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @var string[]
    */
    const NULLABLE_PROPERTIES = [];

    /**
    * Maps param $property to the getter method.
    *
    * @throws UndefinedPropertyException if a property is undefined
    *
    * @return mixed
    */
    public function __get(string $property)
    {
        $expectedMethod = 'Get' . ucfirst($property);
        if (method_exists($this, $expectedMethod) !== true) {
            throw new UndefinedPropertyException(static::class, $property);
        }

        return $this->$expectedMethod();
    }

    /**
    * Maps param $property to the getter method.
    *
    *
    * @param mixed $v
    *
    * @throws UndefinedPropertyException if a property is undefined
    *
    * @return mixed
    */
    public function __set(string $property, $v)
    {
        $expectedMethod = 'Set' . ucfirst($property);
        $expectedGetter = 'Get' . ucfirst($property);
        if (
            method_exists($this, $expectedMethod) !== true
        ) {
            throw new PropertyNotWriteableException(static::class, $property);
        }

        return $this->$expectedMethod($v);
    }

    abstract public function __isset(string $property);

    public function __unset(string $property)
    {
        $this->NudgePropertyValue($property, null);
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property
    * @param mixed $value
    *
    * @throws UndefinedPropertyException if $property is not in static::NULLABLE_PROPERTIES
    */
    abstract protected function NudgePropertyValue(
        string $property,
        $value
    ) : void;

    /**
    * Get the changed properties on an object.
    *
    * @return string[]
    */
    abstract protected function ChangedProperties() : array;

    /**
    * Mark the specified properties as unchanged.
    */
    abstract protected function MakePropertiesUnchanged(
        string ...$properties
    ) : void;

    /**
    * Check if a property exists on an object.
    *
    * @return bool
    */
    abstract protected function HasPropertyChanged(string $property) : bool;
}
