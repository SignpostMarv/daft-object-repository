<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use ReflectionMethod;

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
    * Does some sanity checking.
    *
    * @see DefinesOwnIdPropertiesInterface
    * @see self::CheckTypeDefinesOwnIdProperties()
    */
    public function __construct()
    {
        if (
            ($this instanceof DefinesOwnIdPropertiesInterface)
        ) {
            self::CheckTypeDefinesOwnIdProperties($this);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function __get(string $property)
    {
        return $this->DoGetSet(
            $property,
            'Get' . ucfirst($property),
            PropertyNotReadableException::class,
            NotPublicGetterPropertyException::class
        );
    }

    /**
    * {@inheritdoc}
    */
    public function __set(string $property, $v)
    {
        return $this->DoGetSet(
            $property,
            'Set' . ucfirst($property),
            PropertyNotWriteableException::class,
            NotPublicSetterPropertyException::class,
            $v
        );
    }

    /**
    * {@inheritdoc}
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
    * {@inheritdoc}
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
    * @throws PropertyNotRewriteableException if class is write-once read-many and $property was already changed
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
    * @throws ClassDoesNotImplementClassException if $object is not an implementation of DefinesOwnIdPropertiesInterface
    * @throws ClassMethodReturnHasZeroArrayCountException if $object::DaftObjectIdProperties() does not contain at least one property
    * @throws ClassMethodReturnIsNotArrayOfStringsException if $object::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $object::DaftObjectIdProperties()
    */
    final protected static function CheckTypeDefinesOwnIdProperties(
        DaftObject $object
    ) : void {
        $class = get_class($object);
        if (false === ($object instanceof DefinesOwnIdPropertiesInterface)) {
            throw new ClassDoesNotImplementClassException(
                $class,
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
                $class,
                'DaftObjectIdProperties'
            );
        }

        foreach ($properties as $property) {
            if (false === is_string($property)) {
                throw new ClassMethodReturnIsNotArrayOfStringsException(
                    $class,
                    'DaftObjectIdProperties'
                );
            }
        }
    }

    /**
    * @param mixed $v
    *
    * @return mixed
    */
    private function DoGetSet(
        string $property,
        string $expectedMethod,
        string $notExists,
        string $notPublic,
        $v = null
    ) {
        if (
            (
                'id' !== $property ||
                false === ($this instanceof DefinesOwnIdPropertiesInterface)
            ) &&
            false === in_array($property, static::DaftObjectProperties(), true)
        ) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif (false === method_exists($this, $expectedMethod)) {
            throw new $notExists(
                static::class,
                $property
            );
        } elseif (
            false === (
                new ReflectionMethod(static::class, $expectedMethod)
            )->isPublic()
        ) {
            throw new $notPublic(
                static::class,
                $property
            );
        }

        return $this->$expectedMethod($v);
    }
}
