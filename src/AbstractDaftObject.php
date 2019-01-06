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
    * List of sortable properties for DaftSortableObject.
    *
    * @var array<int, string>
    */
    const SORTABLE_PROPERTIES = [];

    /**
    * @var array<string, array<int, string>>
    */
    const CHANGE_OTHER_PROPERTIES = [];

    /**
    * @var array<string, array<int, string>>
    */
    const PROPERTIES_WITH_MULTI_TYPED_ARRAYS = [];

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

    /**
    * @return mixed
    */
    public function __get(string $property)
    {
        return $this->DoGetSet($property, false);
    }

    /**
    * @param mixed $v
    */
    public function __set(string $property, $v) : void
    {
        $this->DoGetSet($property, true, $v);
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
        * @var array<int, string>
        */
        $properties = array_filter($exportables, function (string $prop) use ($getters) : bool {
            return $this->__isset($prop) && TypeParanoia::MaybeInArray($prop, $getters);
        });

        /**
        * @var array<string, mixed>
        */
        $out = array_combine($properties, array_map(
            /**
            * @return mixed
            */
            function (string $prop) {
                $expectedMethod = TypeUtilities::MethodNameFromProperty($prop);

                return $this->$expectedMethod();
            },
            $properties
        ));

        return $out;
    }

    public function CompareToDaftSortableObject(DaftSortableObject $otherObject) : int
    {
        if ( ! is_a(static::class, DaftSortableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftSortableObject::class
            );
        }

        foreach (static::DaftSortableObjectProperties() as $property) {
            $method = TypeUtilities::MethodNameFromProperty($property);
            $sort = $this->$method() <=> $otherObject->$method();

            if (0 !== $sort) {
                return $sort;
            }
        }

        return 0;
    }

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    public static function DaftObjectProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::PROPERTIES;

        return $out;
    }

    public static function DaftObjectNullableProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::NULLABLE_PROPERTIES;

        return $out;
    }

    public static function DaftObjectExportableProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::EXPORTABLE_PROPERTIES;

        return $out;
    }

    final public static function DaftObjectPublicGetters() : array
    {
        return TypeUtilities::DaftObjectPublicGetters(static::class);
    }

    final public static function DaftObjectPublicOrProtectedGetters() : array
    {
        return TypeUtilities::DaftObjectPublicOrProtectedGetters(static::class);
    }

    final public static function DaftObjectPublicSetters() : array
    {
        return TypeUtilities::DaftObjectPublicSetters(static::class);
    }

    public static function DaftObjectJsonProperties() : array
    {
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        /**
        * @var array<int|string, string>
        */
        $out = static::JSON_PROPERTIES;

        return $out;
    }

    final public static function DaftObjectJsonPropertyNames() : array
    {
        $out = [];

        /**
        * @var array<int|string, string>
        */
        $jsonProperties = static::DaftObjectJsonProperties();

        foreach ($jsonProperties as $k => $prop) {
            if (is_string($k)) {
                $prop = $k;
            }

            $out[] = $prop;
        }

        return $out;
    }

    /**
    * @return array<int, string>
    */
    public static function DaftSortableObjectProperties() : array
    {
        if ( ! is_a(static::class, DaftSortableObject::class, true)) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftSortableObject::class
            );
        }

        /**
        * @var array<int, string>
        */
        $out = static::SORTABLE_PROPERTIES;

        return $out;
    }

    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesChangeOtherProperties() : array
    {
        /**
        * @var array<string, array<int, string>>
        */
        $out = static::CHANGE_OTHER_PROPERTIES;

        return $out;
    }

    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues() : array
    {
        if (
            ! is_a(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                true
            )
        ) {
            throw new ClassDoesNotImplementClassException(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class
            );
        }

        /**
        * @var array<string, array<int, string>>
        */
        $out = static::PROPERTIES_WITH_MULTI_TYPED_ARRAYS;

        return $out;
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property property being nudged
    * @param scalar|array|object|null $value value to nudge property with
    *
    * @throws UndefinedPropertyException if $property is not in static::DaftObjectProperties()
    * @throws PropertyNotNullableException if $property is not in static::DaftObjectNullableProperties()
    * @throws PropertyNotRewriteableException if class is write-once read-many and $property was already changed
    */
    abstract protected function NudgePropertyValue(string $property, $value) : void;

    protected function MaybeThrowOnDoGetSet(string $property, bool $setter, array $props) : void
    {
        if ( ! TypeParanoia::MaybeInArray($property, $props)) {
            if ( ! TypeParanoia::MaybeInArray($property, static::DaftObjectProperties())) {
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
