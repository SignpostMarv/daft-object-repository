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
        * @var array<int, string>
        */
        $properties = static::DaftObjectJsonPropertyNames();

        foreach ($properties as $property) {
            /**
            * @var scalar|array|object|null
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
                * @var string|null
                */
                $jsonType = $jsonDef[$prop] ?? null;

                if ( ! is_string($jsonType)) {
                    return $array[$prop];
                }

                return JsonTypeUtilities::DaftJsonFromJsonType(
                    $jsonType,
                    $prop,
                    (array) $array[$prop],
                    $writeAll
                );
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
        * @var array<int, string>
        */
        $properties = static::NULLABLE_PROPERTIES;

        if (
            ! array_key_exists($property, $this->data) &&
            ! in_array($property, $properties, true)
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        } elseif (in_array($property, $properties, true)) {
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
        bool $autoTrimStrings = false,
        bool $throwIfNotUnique = false
    ) : void {
        /**
        * @var array<int, string>
        */
        $nullables = static::NULLABLE_PROPERTIES;

        $this->MaybeThrowForPropertyOnNudge($property);
        $this->MaybeThrowOnNudge($property, $value, $nullables);

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
            $value = $this->MaybeThrowIfValueDoesNotMatchMultiTypedArray(
                $property,
                $autoTrimStrings,
                $throwIfNotUnique,
                $value,
                ...$spec
            );
        }

        if (is_string($value) && $autoTrimStrings) {
            $value = trim($value);
        }

        $isChanged = (
            ! array_key_exists($property, $this->data) ||
            $this->data[$property] !== $value
        );

        $this->data[$property] = $value;

        if ($isChanged && true !== isset($this->changedProperties[$property])) {
            $this->changedProperties[$property] = $this->wormProperties[$property] = true;
        }
    }

    /**
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    protected function MaybeThrowForPropertyOnNudge(string $property) : void
    {
        /**
        * @var array<int, string>
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
    protected function MaybeThrowOnNudge(string $property, $value, array $properties) : void
    {
        if (true === is_null($value) && true !== in_array($property, $properties, true)) {
            throw new PropertyNotNullableException(static::class, $property);
        }
    }

    /**
    * @param mixed $value
    *
    * @return array<int, mixed> filtered $value
    */
    protected function MaybeThrowIfValueDoesNotMatchMultiTypedArray(
        string $prop,
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        $value,
        string ...$types
    ) : array {
        if ( ! is_array($value)) {
            throw new InvalidArgumentException(
                'Argument 4 passed to ' .
                __METHOD__ .
                ' must be an array, ' .
                (is_object($value) ? get_class($value) : gettype($value)) .
                ' given!'
            );
        }

        $initialCount = count($value);
        $value = array_filter($value, 'is_int', ARRAY_FILTER_USE_KEY);

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 4 passed to ' .
                __METHOD__ .
                ' must be array<int, mixed>'
            );
        }

        $initialCount = count($value);

        $value = array_filter(
            $value,
            /**
            * @param mixed $maybe
            */
            function ($maybe) use ($types) : bool {
                if (is_object($maybe)) {
                    foreach ($types as $maybeType) {
                        if (is_a($maybe, $maybeType)) {
                            return true;
                        }
                    }

                    return false;
                }

                return in_array(gettype($maybe), $types, true);
            }
        );

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 4 passed to ' .
                __METHOD__ .
                ' contained values that did not match the provided types!'
            );
        }

        $initialCount = count($value);

        if (in_array('string', $types, true) && $autoTrimStrings && $initialCount > 0) {
            $value = array_map(
                /**
                * @param mixed $maybe
                *
                * @return mixed
                */
                function ($maybe) {
                    return is_string($maybe) ? trim($maybe) : $maybe;
                },
                $value
            );
        }

        $value = array_unique($value, SORT_REGULAR);

        if ($throwIfNotUnique && count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained non-unique values!'
            );
        }

        return array_values($value);
    }
}
