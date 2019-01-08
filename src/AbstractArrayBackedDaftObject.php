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
    * @param array<int|string, scalar|array|object|null> $data
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
        * @var array<int, string>|string
        */
        $properties = static::PROPERTIES;

        return
            TypeParanoia::MaybeInMaybeArray($property, $properties) &&
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
        $array = JsonTypeUtilities::ThrowIfJsonDefNotValid(static::class, $array);

        /**
        * @var array<int, string>
        */
        $props = array_keys($array);
        $mapper = static::DaftJsonClosure($array, $writeAll);

        /**
        * @var array<int, scalar|object|array|null>
        */
        $vals = array_map($mapper, $props);

        $out = new static(array_combine($props, $vals), $writeAll);

        return JsonTypeUtilities::ThrowIfDaftObjectObjectNotDaftJson($out);
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        return static::DaftObjectFromJsonArray(TypeParanoia::ForceArgumentAsArray(json_decode(
            $string,
            true
        )));
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
        $isNullable = TypeParanoia::MaybeInMaybeArray($property, static::NULLABLE_PROPERTIES);

        if (
            ! array_key_exists($property, $this->data) &&
            ! $isNullable
        ) {
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
        $nullables = static::NULLABLE_PROPERTIES;

        $this->MaybeThrowForPropertyOnNudge($property);
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
                    TypeParanoia::ForceArgumentAsArray($array[$prop]),
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
        /**
        * @var array<int, string>|null
        */
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
            $value = TypeParanoia::MaybeThrowIfValueDoesNotMatchMultiTypedArray(
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
    private function MaybeThrowForPropertyOnNudge(string $property) : void
    {
        /**
        * @var array<int, string>
        */
        $properties = static::PROPERTIES;

        if ( ! TypeParanoia::MaybeInArray($property, $properties)) {
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
        if (true === is_null($value) && ! TypeParanoia::MaybeInArray($property, $properties)) {
            throw new PropertyNotNullableException(static::class, $property);
        }
    }
}
