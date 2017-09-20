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
        return $this->DoGetSet($property, true);
    }

    /**
    * {@inheritdoc}
    */
    public function __set(string $property, $v)
    {
        return $this->DoGetSet($property, false, $v);
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
    }

    /**
    * @param mixed $v
    *
    * @return mixed
    */
    private function DoGetSet(
        string $property,
        bool $getNotSet,
        $v = null
    ) {
        static $scopes = [];
        $expectedMethod = ($getNotSet ? 'Get' : 'Set') . ucfirst($property);

        if (
            false === (
                'id' === $property &&
                is_a(
                    static::class,
                    DefinesOwnIdPropertiesInterface::class,
                    true
                )
            ) &&
            false === in_array($property, static::DaftObjectProperties(), true)
        ) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif (false === method_exists($this, $expectedMethod)) {
            if ($getNotSet) {
                throw new PropertyNotReadableException(
                    static::class,
                    $property
                );
            }
            throw new PropertyNotWriteableException(
                static::class,
                $property
            );
        } elseif (false === $this->CheckPublicScope($expectedMethod)) {
            if ($getNotSet) {
                throw new NotPublicGetterPropertyException(
                    static::class,
                    $property
                );
            }
            throw new NotPublicSetterPropertyException(
                static::class,
                $property
            );
        }

        return $this->$expectedMethod($v);
    }

    private function CheckPublicScope(string $expectedMethod) : bool
    {
        static $scopes = [];
        if (false === isset($scopes[$expectedMethod])) {
            $scopes[$expectedMethod] = (
                new ReflectionMethod(static::class, $expectedMethod)
            )->isPublic();
        }

        return $scopes[$expectedMethod];
    }
}
