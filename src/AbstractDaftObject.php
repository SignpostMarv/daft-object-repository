<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use TypeError;

/**
* Base daft object.
*/
abstract class AbstractDaftObject implements DaftObject
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
    * Index of checked types.
    *
    * @see self::CheckTypeDefinesOwnIdProperties()
    *
    * @var bool[]
    */
    private static $checkedTypes = [];

    /**
    * Does some sanity checking.
    *
    * @see DefinesOwnIdPropertiesInterface
    * @see self::CheckTypeDefinesOwnIdProperties()
    *
    * @throws TypeError if static::class was previously determined to be incorrectly implemented
    */
    public function __construct()
    {
        if (
            ($this instanceof DefinesOwnIdPropertiesInterface) &&
            false === self::CheckTypeDefinesOwnIdProperties($this)
        ) {
            throw new AlreadyIncorrectlyImplementedTypeError(
                get_class($this) // phpunit coverage does not pick up static::class here
            );
        }
    }

    /**
    * Maps param $property to the getter method.
    *
    * @param string $property the property being retrieved
    *
    * @throws UndefinedPropertyException if a property is undefined
    *
    * @return mixed
    */
    public function __get(string $property)
    {
        $expectedMethod = 'Get' . ucfirst($property);
        if (true !== method_exists($this, $expectedMethod)) {
            throw new UndefinedPropertyException(static::class, $property);
        }

        return $this->$expectedMethod();
    }

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
    public function __set(string $property, $v)
    {
        $expectedMethod = 'Set' . ucfirst($property);
        if (
            true !== method_exists($this, $expectedMethod)
        ) {
            throw new PropertyNotWriteableException(static::class, $property);
        }

        return $this->$expectedMethod($v);
    }

    /**
    * required to support unset($foo->bar).
    *
    * @param string $property the property being unset
    *
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void
    {
        $this->NudgePropertyValue($property, null);
    }

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return string[]
    */
    final public static function DaftObjectProperties() : array
    {
        return static::PROPERTIES;
    }

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @return string[]
    */
    final public static function DaftObjectNullableProperties() : array
    {
        return static::NULLABLE_PROPERTIES;
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property property being nudged
    * @param mixed $value value to nudge property with
    *
    * @throws UndefinedPropertyException if $property is not in static::DaftObjectProperties()
    * @throws PropertyNotNullableException if $property is not in static::DaftObjectNullableProperties()
    */
    abstract protected function NudgePropertyValue(
        string $property,
        $value
    ) : void;

    /**
    * Checks if a type correctly defines it's own id.
    *
    * @param DaftObject $object
    *
    * @throws TypeError if $object::DaftObjectIdProperties() does not contain at least one property
    * @throws TypeError if $object::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $object::DaftObjectIdProperties()
    */
    final protected static function CheckTypeDefinesOwnIdProperties(
        DaftObject $object
    ) : bool {
        static $checkedTypes = [];

        if (false === isset($checkedTypes[get_class($object)])) {
            $checkedTypes[get_class($object)] = false;

            if (false === ($object instanceof DefinesOwnIdPropertiesInterface)) {
                throw new ClassDoesNotImplementClassException(
                    get_class($object),
                    DefinesOwnIdPropertiesInterface::class
                );
            }

            /**
            * @var DefinesOwnIdPropertiesInterface $object
            */
            $object = $object;

            $properties = $object::DaftObjectIdProperties();

            if (count($properties) < 1) {
                throw new ClassMethodReturnHasZeroArrayCountException(
                    get_class($object),
                    'DaftObjectIdProperties'
                );
            }

            foreach ($properties as $property) {
                if (false === is_string($property)) {
                    throw new ClassMethodReturnIsNotArrayOfStringsException(
                        get_class($object),
                        'DaftObjectIdProperties'
                    );
                } elseif (
                    false === in_array(
                        $property,
                        $object::DaftObjectProperties(),
                        true
                    )
                ) {
                    throw new UndefinedPropertyException(
                        get_class($object),
                        $property
                    );
                }
            }

            $checkedTypes[get_class($object)] = true;
        }

        return $checkedTypes[get_class($object)];
    }
}
