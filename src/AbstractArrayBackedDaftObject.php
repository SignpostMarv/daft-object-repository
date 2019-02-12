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
    const BOOL_DEFAULT_WRITEALL = false;

    const BOOL_DEFAULT_AUTOTRIMSTRINGS = false;

    const BOOL_DEFAULT_THROWIFNOTUNIQUE = false;

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
    * @param array<string, scalar|array|object|null> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
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
            in_array(
                $property,
                static::DaftObjectProperties(),
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            ) &&
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
        /**
        * @var array<int, string>
        */
        $properties = static::DaftObjectJsonPropertyNames();

        /**
        * @var array<string, string>
        */
        $properties = array_combine($properties, $properties);

        return array_filter(
            array_map(
                /**
                * @return mixed
                */
                function (string $property) {
                    return $this->DoGetSet($property, false);
                },
                $properties
            ),
            /**
            * @param mixed $maybe
            */
            function ($maybe) : bool {
                return ! is_null($maybe);
            }
        );
    }

    final public static function DaftObjectFromJsonArray(
        array $array,
        bool $writeAll = self::BOOL_DEFAULT_WRITEALL
    ) : DaftJson {
        $type = JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        $array = JsonTypeUtilities::ThrowIfJsonDefNotValid($type, $array);

        /**
        * @var array<int, string>
        */
        $props = array_keys($array);
        $mapper = static::DaftJsonClosure($array, $writeAll);

        /**
        * @var array<int, scalar|object|array|null>
        */
        $vals = array_map($mapper, $props);

        return new $type(array_combine($props, $vals), $writeAll);
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        /**
        * @var scalar|array|object|null
        */
        $decoded = json_decode($string, true);

        return JsonTypeUtilities::ThrowIfNotDaftJson(static::class)::DaftObjectFromJsonArray(
            is_array($decoded) ? $decoded : [$decoded]
        );
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
        $isNullable = in_array(
            $property,
            static::DaftObjectNullableProperties(),
            DefinitionAssistant::IN_ARRAY_STRICT_MODE
        );

        if ( ! array_key_exists($property, $this->data) && ! $isNullable) {
            throw new PropertyNotNullableException(static::class, $property);
        } elseif ($isNullable) {
            return $this->data[$property] ?? null;
        }

        return $this->data[$property];
    }

    /**
    * @param scalar|array|object|null $value
    */
    protected function NudgePropertyValue(
        string $property,
        $value,
        bool $autoTrimStrings = self::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = self::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) : void {
        /**
        * @var array<int, string>
        */
        $nullables = static::DaftObjectNullableProperties();

        $this->MaybeThrowOnNudge($property, $value, $nullables);

        $value = $this->MaybeModifyValueBeforeNudge(
            $property,
            $value,
            $autoTrimStrings,
            $throwIfNotUnique
        );

        $isChanged = (
            ! array_key_exists($property, $this->data) ||
            $this->data[$property] !== $value
        );

        $this->data[$property] = $value;

        if ($isChanged && true !== isset($this->changedProperties[$property])) {
            $this->changedProperties[$property] = $this->wormProperties[$property] = true;
        }
    }

    private static function DaftJsonClosure(array $array, bool $writeAll) : Closure
    {
        $jsonDef = static::DaftObjectJsonProperties();

        return
            /**
            * @return mixed
            */
            function (string $prop) use ($array, $jsonDef, $writeAll) {
                /**
                * @var string|null
                */
                $jsonType = $jsonDef[$prop] ?? null;

                if ( ! is_string($jsonType)) {
                    return $array[$prop];
                }

                return JsonTypeUtilities::DaftJsonFromJsonType(
                    $jsonType,
                    $prop,
                    (is_array($array[$prop]) ? $array[$prop] : [$array[$prop]]),
                    $writeAll
                );
            };
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @return scalar|array|object|null
    */
    private function MaybeModifyValueBeforeNudge(
        string $property,
        $value,
        bool $autoTrimStrings = self::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = self::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) {
        $spec = null;

        if (
            is_a(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                true
            )
        ) {
            $spec = (
                static::DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues()[$property] ?? null
            );
        }

        if (is_array($spec)) {
            $value = DefinitionAssistant::MaybeThrowIfValueDoesNotMatchMultiTypedArray(
                $autoTrimStrings,
                $throwIfNotUnique,
                $value,
                ...$spec
            );
        }

        if (is_string($value) && $autoTrimStrings) {
            $value = trim($value);
        }

        return $value;
    }

    /**
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    private function MaybeThrowForPropertyOnNudge(string $property) : string
    {
        $properties = static::DaftObjectProperties();

        if ( ! in_array($property, $properties, DefinitionAssistant::IN_ARRAY_STRICT_MODE)) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif ($this->DaftObjectWormPropertyWritten($property)) {
            throw new PropertyNotRewriteableException(static::class, $property);
        }

        return $property;
    }

    /**
    * @param mixed $value
    *
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    private function MaybeThrowOnNudge(string $property, $value, array $properties) : void
    {
        $property = $this->MaybeThrowForPropertyOnNudge($property);

        if (
            true === is_null($value) &&
            ! in_array($property, $properties, DefinitionAssistant::IN_ARRAY_STRICT_MODE)
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        }
    }
}
