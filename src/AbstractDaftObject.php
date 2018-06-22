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
abstract class AbstractDaftObject implements DaftObject
{
    /**
    * List of properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const PROPERTIES = [];

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const NULLABLE_PROPERTIES = [];

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const EXPORTABLE_PROPERTIES = [];

    /**
    * import/export definition for DaftJson.
    *
    * @var array<int, string>
    */
    const JSON_PROPERTIES = [];

    /**
    * Does some sanity checking.
    *
    * @see DefinesOwnIdPropertiesInterface
    * @see TypeUtilities::CheckTypeDefinesOwnIdProperties()
    */
    public function __construct()
    {
        TypeUtilities::CheckTypeDefinesOwnIdProperties(
            static::class,
            ($this instanceof DefinesOwnIdPropertiesInterface)
        );
    }

    public function __get(string $property)
    {
        return $this->DoGetSet($property, false);
    }

    public function __set(string $property, $v)
    {
        return $this->DoGetSet($property, true, $v);
    }

    /**
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void
    {
        $this->NudgePropertyValue($property, null);
    }

    /**
    * @return array<string, mixed>
    */
    public function __debugInfo() : array
    {
        $getters = static::DaftObjectPublicGetters();
        $exportables = static::DaftObjectExportableProperties();
        /**
        * @var array<int, string> $properties
        */
        $properties = array_filter($exportables, function (string $prop) use ($getters) : bool {
            return $this->__isset($prop) && in_array($prop, $getters, true);
        });

        return array_combine($properties, array_map(
            /**
            * @return mixed
            */
            function (string $prop) {
                $expectedMethod = 'Get' . ucfirst($prop);

                return $this->$expectedMethod();
            },
            $properties
        ));
    }

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    final public static function DaftObjectProperties() : array
    {
        return static::PROPERTIES;
    }

    final public static function DaftObjectNullableProperties() : array
    {
        return static::NULLABLE_PROPERTIES;
    }

    final public static function DaftObjectExportableProperties() : array
    {
        return static::EXPORTABLE_PROPERTIES;
    }

    final public static function DaftObjectPublicGetters() : array
    {
        return TypeUtilities::DaftObjectPublicGetters(static::class);
    }

    final public static function DaftObjectPublicSetters() : array
    {
        return TypeUtilities::DaftObjectPublicSetters(static::class);
    }

    final public static function DaftObjectJsonProperties() : array
    {
        TypeUtilities::ThrowIfNotDaftJson(static::class);

        return static::JSON_PROPERTIES;
    }

    final public static function DaftObjectJsonPropertyNames() : array
    {
        $out = [];

        foreach (static::DaftObjectJsonProperties() as $k => $prop) {
            if (is_string($k)) {
                $prop = $k;
            }

            $out[] = $prop;
        }

        return $out;
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
    abstract protected function NudgePropertyValue(string $property, $value) : void;

    protected function MaybeThrowOnDoGetSet(string $property, bool $setter, array $props) : void
    {
        if (false === in_array($property, $props, true)) {
            if (false === in_array($property, static::DaftObjectProperties(), true)) {
                throw new UndefinedPropertyException(static::class, $property);
            } elseif ($setter) {
                throw new NotPublicSetterPropertyException(static::class, $property);
            }

            throw new NotPublicGetterPropertyException(static::class, $property);
        }
    }

    /**
    * @param mixed $v
    *
    * @return mixed
    */
    protected function DoGetSet(string $property, bool $setter, $v = null)
    {
        $props = $setter ? static::DaftObjectPublicSetters() : static::DaftObjectPublicGetters();

        $this->MaybeThrowOnDoGetSet($property, $setter, $props);

        return $this->{TypeUtilities::MethodNameFromProperty($property, $setter)}($v);
    }
}
