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

    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct();

        if (true === $writeAll) {
            foreach ($data as $k => $v) {
                $this->__set($k, $v);
            }
        } else {
            foreach ($data as $k => $v) {
                if ( ! is_string($k)) {
                    throw new InvalidArgumentException('Properties must be strings!');
                }
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
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

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
        $array = JsonTypeUtilities::ThrowIfJsonDefNotValid(static::class, $array);
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
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        return static::DaftObjectFromJsonArray(json_decode($string, true));
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
                $jsonType = $jsonDef[$prop] ?? null;

                if ( ! is_string($jsonType)) {
                    return $array[$prop];
                }

                return JsonTypeUtilities::DaftJsonFromJsonType($jsonType, $prop, $array[$prop], $writeAll);
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

    /**
    * @param mixed $value
    */
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
}
