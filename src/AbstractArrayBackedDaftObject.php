<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;
use InvalidArgumentException;

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

    /**
    * @param array<int|string, scalar|null|array|object> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct();

        if (true === $writeAll) {
            foreach ($data as $k => $v) {
                if ( ! is_string($k)) {
                    throw new InvalidArgumentException(DaftObjectCreatedByArray::ERR_KEY_NOT_STRING);
                }
                $this->__set($k, $v);
            }
        } else {
            foreach ($data as $k => $v) {
                if ( ! is_string($k)) {
                    throw new InvalidArgumentException(DaftObjectCreatedByArray::ERR_KEY_NOT_STRING);
                }
                $this->data[$k] = $v;
            }
        }
    }

    public function __isset(string $property) : bool
    {
        /**
        * @var array<int, string>|string $properties
        */
        $properties = static::PROPERTIES;

        return
            in_array($property, (array) $properties, true) &&
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
        return $this->changedProperties[$property] ?? false;
    }

    public function jsonSerialize() : array
    {
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        $out = [];

        /**
        * @var array<int, string> $properties
        */
        $properties = static::DaftObjectJsonPropertyNames();

        foreach ($properties as $property) {
            /**
            * @var scalar|null|array|object $val
            */
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
        $array = JsonTypeUtilities::ThrowIfJsonDefNotValid(static::class, $array);

        /**
        * @var array<int, string> $props
        */
        $props = array_keys($array);
        $mapper = static::DaftJsonClosure($array, $writeAll);

        /**
        * @var array<int, scalar|null|object|array> $vals
        */
        $vals = array_map($mapper, $props);

        /**
        * @var DaftJson $out
        */
        $out = new static(array_combine($props, $vals), $writeAll);

        return $out;
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        return static::DaftObjectFromJsonArray((array) json_decode($string, true));
    }

    public function DaftObjectWormPropertyWritten(string $property) : bool
    {
        $wormProperties = $this->wormProperties;

        return
            ($this instanceof DaftObjectWorm) &&
            (
                $this->HasPropertyChanged($property) ||
                isset($wormProperties[$property])
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
                /**
                * @var string|null $jsonType
                */
                $jsonType = $jsonDef[$prop] ?? null;

                if ( ! is_string($jsonType)) {
                    return $array[$prop];
                }

                return JsonTypeUtilities::DaftJsonFromJsonType($jsonType, $prop, (array) $array[$prop], $writeAll);
            };
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
        /**
        * @var array<int, string> $properties
        */
        $properties = static::NULLABLE_PROPERTIES;

        if (
            false === array_key_exists($property, $this->data) &&
            false === in_array($property, $properties, true)
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        } elseif (
            in_array($property, $properties, true)
        ) {
            return $this->data[$property] ?? null;
        }

        return $this->data[$property];
    }

    /**
    * @param scalar|null|array|object $value
    */
    protected function NudgePropertyValue(string $property, $value) : void
    {
        /**
        * @var array<int, string> $nullables
        */
        $nullables = static::NULLABLE_PROPERTIES;

        $this->MaybeThrowForPropertyOnNudge($property);
        $this->MaybeThrowOnNudge($property, $value, $nullables);

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
        /**
        * @var array<int, string> $properties
        */
        $properties = static::PROPERTIES;

        if (true !== in_array($property, $properties, true)) {
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
}
