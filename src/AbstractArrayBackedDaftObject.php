<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;

/**
* Array-backed daft objects.
*/
abstract class AbstractArrayBackedDaftObject extends AbstractDaftObject implements DaftObjectCreatedByArray
{
    /**
    * data for this instance.
    *
    * @var array<string, mixed>
    */
    private $data = [];

    /**
    * List of changed properties.
    *
    * @var array<string, bool>
    */
    private $changedProperties = [];

    /**
    * List of changed properties, for write-once read-many.
    *
    * @var array<string, bool>
    */
    private $wormProperties = [];

    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct();

        if (true === $writeAll) {
            foreach ($data as $k => $v) {
                $this->__set($k, $v);
            }
        } else {
            foreach ($data as $k => $v) {
                $this->data[$k] = $v;
            }
        }
    }

    public function __isset(string $property) : bool
    {
        return
            in_array($property, static::PROPERTIES, true) &&
            isset($this->data, $this->data[$property]);
    }

    public function ChangedProperties() : array
    {
        return array_keys($this->changedProperties);
    }

    public function MakePropertiesUnchanged(string ...$properties) : void
    {
        foreach ($properties as $property) {
            unset($this->changedProperties[$property]);
        }
    }

    public function HasPropertyChanged(string $property) : bool
    {
        return
            isset($this->changedProperties[$property]) &&
            true === $this->changedProperties[$property];
    }

    public function jsonSerialize() : array
    {
        TypeUtilities::ThrowIfNotDaftJson(static::class);

        $out = [];

        foreach (static::DaftObjectJsonPropertyNames() as $property) {
            $val = $this->DoGetSet($property, false);

            if (false === is_null($val)) {
                $out[$property] = $val;
            }
        }

        return $out;
    }

    final public static function DaftObjectFromJsonArray(
        array $array,
        bool $writeAll = false
    ) : DaftJson {
        TypeUtilities::ThrowIfNotDaftJson(static::class);
        $array = static::ThrowIfJsonDefNotValid($array);
        $props = array_keys($array);
        $mapper = static::DaftJsonClosure($array, $writeAll);

        /**
        * @var DaftJson $out
        */
        $out = new static(array_combine($props, array_map($mapper, $props)), $writeAll);

        return $out;
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        TypeUtilities::ThrowIfNotDaftJson(static::class);

        return static::DaftObjectFromJsonArray(json_decode($string, true));
    }

    public function DaftObjectWormPropertyWritten(string $property) : bool
    {
        $wormProperties = $this->wormProperties;

        return
            ($this instanceof DaftObjectWorm) &&
            (
                $this->HasPropertyChanged($property) ||
                false === empty($wormProperties[$property])
            );
    }

    final protected static function DaftJsonClosure(array $array, bool $writeAll) : Closure
    {
        $jsonDef = static::DaftObjectJsonProperties();

        return
            /**
            * @return mixed
            */
            function (string $prop) use ($array, $jsonDef, $writeAll) {
                $jsonType = $jsonDef[$prop] ?? null;

                if ( ! is_string($jsonType)) {
                    return $array[$prop];
                }

                return static::DaftJsonFromJsonType($jsonType, $prop, $array[$prop], $writeAll);
            };
    }

    /**
    * @return array<int, DaftJson>|DaftJson
    */
    final protected static function DaftJsonFromJsonType(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) {
        if ('[]' === mb_substr($jsonType, -2)) {
            $jsonType = mb_substr($jsonType, 0, -2);

            return static::DaftObjectFromJsonTypeArray($jsonType, $prop, $propVal, $writeAll);
        }

        return static::DaftObjectFromJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @param array<string, mixed> $propVal
    *
    * @return DaftJson
    */
    protected static function DaftObjectFromJsonType(string $type, array $propVal, bool $writeAll)
    {
        TypeUtilities::ThrowIfNotJsonType($type);

        return static::ArrayToJsonType($type, $propVal, $writeAll);
    }

    /**
    * @return array<int, DaftJson>
    */
    protected static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        TypeUtilities::ThrowIfNotJsonType($jsonType);

        $out = [];

        foreach ($propVal as $val) {
            if (false === is_array($val)) {
                throw new PropertyNotJsonDecodableShouldBeArrayException($jsonType, $prop);
            }
            $out[] = static::ArrayToJsonType($jsonType, $val, $writeAll);
        }

        return $out;
    }

    /**
    * Retrieve a property from data.
    *
    * @param string $property the property being retrieved
    *
    * @throws PropertyNotNullableException if value is not set and $property is not listed as nullabe
    *
    * @return mixed the property value
    */
    protected function RetrievePropertyValueFromData(string $property)
    {
        if (
            false === array_key_exists($property, $this->data) &&
            false === in_array($property, static::NULLABLE_PROPERTIES, true)
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        } elseif (
            in_array($property, static::NULLABLE_PROPERTIES, true)
        ) {
            return $this->data[$property] ?? null;
        }

        return $this->data[$property];
    }

    protected function NudgePropertyValue(string $property, $value) : void
    {
        $this->MaybeThrowForPropertyOnNudge($property);
        $this->MaybeThrowOnNudge($property, $value, static::NULLABLE_PROPERTIES);

        $isChanged = (
            false === array_key_exists($property, $this->data) ||
            $this->data[$property] !== $value
        );

        $this->data[$property] = $value;

        if ($isChanged && true !== isset($this->changedProperties[$property])) {
            $this->changedProperties[$property] = true;
            $this->wormProperties[$property] = true;
        }
    }

    /**
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    private function MaybeThrowForPropertyOnNudge(string $property) : void
    {
        if (true !== in_array($property, static::PROPERTIES, true)) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif ($this->DaftObjectWormPropertyWritten($property)) {
            throw new PropertyNotRewriteableException(static::class, $property);
        }
    }

    /**
    * @param mixed $value
    *
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    private function MaybeThrowOnNudge(string $property, $value, array $properties) : void
    {
        if (true === is_null($value) && true !== in_array($property, $properties, true)) {
            throw new PropertyNotNullableException(static::class, $property);
        }
    }

    private static function FilterThrowIfJsonDefNotValid(array $array) : array
    {
        $jsonProps = static::DaftObjectJsonPropertyNames();

        $filter = function (string $prop) use ($jsonProps, $array) : bool {
            if (false === in_array($prop, $jsonProps, true)) {
                throw new PropertyNotJsonDecodableException(static::class, $prop);
            }

            return false === is_null($array[$prop]);
        };

        return array_filter($array, $filter, ARRAY_FILTER_USE_KEY);
    }

    private static function ThrowIfJsonDefNotValid(array $array) : array
    {
        $array = static::FilterThrowIfJsonDefNotValid($array);

        $keys = array_keys($array);

        return array_combine(
            $keys,
            array_map(static::MakeMapperThrowIfJsonDefNotValid($array), $keys)
        );
    }

    private static function MakeMapperThrowIfJsonDefNotValid(array $array) : Closure
    {
        $jsonDef = static::DaftObjectJsonProperties();

        $mapper =
            /**
            * @return mixed
            */
            function (string $prop) use ($jsonDef, $array) {
                if (isset($jsonDef[$prop]) && false === is_array($array[$prop])) {
                    static::ThrowBecauseArrayJsonTypeNotValid($jsonDef[$prop], $prop);
                }

                return $array[$prop];
            };

        return $mapper;
    }

    private static function ThrowBecauseArrayJsonTypeNotValid(string $type, string $prop) : void
    {
        if ('[]' === mb_substr($type, -2)) {
            throw new PropertyNotJsonDecodableShouldBeArrayException(static::class, $prop);
        }
        throw new PropertyNotJsonDecodableShouldBeArrayException($type, $prop);
    }

    private static function ArrayToJsonType(string $type, array $value, bool $writeAll) : DaftJson
    {
        /**
        * @var DaftJson $type
        */
        $type = $type;

        return $type::DaftObjectFromJsonArray($value, $writeAll);
    }
}
