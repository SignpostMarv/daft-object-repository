<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Array-backed daft objects.
*/
abstract class AbstractArrayBackedDaftObject extends AbstractDaftObject implements DaftObjectCreatedByArray
{
    /**
    * data for this instance.
    *
    * @var array
    */
    private $data = [];

    /**
    * List of changed properties.
    *
    * @var bool[]
    */
    private $changedProperties = [];

    /**
    * List of changed properties, for write-once read-many.
    *
    * @var bool[]
    */
    private $wormProperties = [];

    /**
    * {@inheritdoc}
    */
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

    /**
    * {@inheritdoc}
    */
    public function __isset(string $property) : bool
    {
        return
            in_array($property, static::PROPERTIES, true) &&
            isset($this->data, $this->data[$property]);
    }

    /**
    * {@inheritdoc}
    */
    public function ChangedProperties() : array
    {
        /**
        * @var string[] $out
        */
        $out = array_keys($this->changedProperties);

        return $out;
    }

    /**
    * {@inheritdoc}
    */
    public function MakePropertiesUnchanged(string ...$properties) : void
    {
        foreach ($properties as $property) {
            unset($this->changedProperties[$property]);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function HasPropertyChanged(string $property) : bool
    {
        return
            isset($this->changedProperties[$property]) &&
            true === $this->changedProperties[$property];
    }

    /**
    * {@inheritdoc}
    */
    public function jsonSerialize() : array
    {
        static::ThrowIfNotDaftJson();

        $out = [];

        foreach (static::DaftObjectJsonProperties() as $k => $v) {
            $property = $v;
            if (is_string($k)) {
                $property = $k;
            }

            $val = $this->DoGetSet(
                $property,
                'Get' . ucfirst($property),
                PropertyNotReadableException::class,
                NotPublicGetterPropertyException::class
            );

            if (false === is_null($val)) {
                $out[$property] = $val;
            }
        }

        return $out;
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectFromJsonArray(
        array $array,
        bool $writeAll = false
    ) : DaftJson {
        static::ThrowIfNotDaftJson();
        $array = static::ThrowIfJsonDefNotValid($array);
        $in = [];

        $jsonDef = static::DaftObjectJsonProperties();

        foreach (array_keys($array) as $prop) {
            if (isset($jsonDef[$prop])) {
                $jsonType = $jsonDef[$prop];

                if ('[]' === mb_substr($jsonType, -2)) {
                    $in[$prop] = static::DaftObjectFromJsonTypeArray(
                        mb_substr($jsonType, 0, -2),
                        $prop,
                        $array[$prop],
                        $writeAll
                    );
                } else {
                    $in[$prop] = static::DaftObjectFromJsonType(
                        $jsonType,
                        $array[$prop],
                        $writeAll
                    );
                }
            } else {
                $in[$prop] = $array[$prop];
            }
        }

        /**
        * @var DaftJson $out
        */
        $out = new static($in, $writeAll);

        return $out;
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        static::ThrowIfNotDaftJson();

        return static::DaftObjectFromJsonArray(json_decode($string, true));
    }

    /**
    * @param mixed $propVal
    *
    * @return DaftJson
    */
    protected static function DaftObjectFromJsonType(
        string $jsonType,
        array $propVal,
        bool $writeAll
    ) {
        static::ThrowIfNotJsonType($jsonType);

        return static::ArrayToJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @return DaftJson[]
    */
    protected static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        static::ThrowIfNotJsonType($jsonType);

        $out = [];

        foreach ($propVal as $val) {
            if (false === is_array($val)) {
                throw new PropertyNotJsonDecodableShouldBeArrayException(
                    $jsonType,
                    $prop
                );
            }
            $out[] = static::ArrayToJsonType(
                $jsonType,
                $val,
                $writeAll
            );
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

    /**
    * {@inheritdoc}
    */
    protected function NudgePropertyValue(string $property, $value) : void
    {
        $this->MaybeThrowOnNudge($property, $value);

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
    * @param mixed $value
    *
    * @see AbstractArrayBackedDaftObject::NudgePropertyValue()
    */
    private function MaybeThrowOnNudge(string $property, $value) : void
    {
        if (true !== in_array($property, static::PROPERTIES, true)) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif (
            true === is_null($value) &&
            true !== in_array($property, static::NULLABLE_PROPERTIES, true)
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        } elseif (
            $this instanceof DaftObjectWorm &&
            (
                $this->HasPropertyChanged($property) ||
                false === empty($this->wormProperties[$property])
            )
        ) {
            throw new PropertyNotRewriteableException(
                static::class,
                $property
            );
        }
    }

    private static function ThrowIfJsonDefNotValid(array $array) : array
    {
        $jsonProps = [];

        $jsonDef = static::DaftObjectJsonProperties();
        $nullableProps = static::DaftObjectNullableProperties();

        foreach ($jsonDef as $k => $v) {
            $prop = $v;
            if (is_string($k)) {
                $prop = $k;
            }
            if (
                (
                    false === isset($array[$prop]) ||
                    is_null($array[$prop])
                ) &&
                false === in_array($prop, $nullableProps, true)
            ) {
                throw new PropertyNotNullableException(static::class, $prop);
            }

            $jsonProps[] = $prop;
        }

        $out = [];

        foreach ($array as $prop => $propVal) {
            if (
                false === in_array($prop, $jsonProps, true)
            ) {
                throw new PropertyNotJsonDecodableException(
                    static::class,
                    $prop
                );
            } elseif (false === is_null($propVal)) {
                if (isset($jsonDef[$prop])) {
                    $jsonType = $jsonDef[$prop];

                    if (false === is_array($propVal)) {
                        if ('[]' === mb_substr($jsonType, -2)) {
                            throw new PropertyNotJsonDecodableShouldBeArrayException(
                                static::class,
                                $prop
                            );
                        }
                        throw new PropertyNotJsonDecodableShouldBeArrayException(
                            $jsonType,
                            $prop
                        );
                    }
                }
                $out[$prop] = $propVal;
            }
        }

        return $out;
    }

    private static function ThrowIfNotJsonType(string $jsonType) : void
    {
        if (false === is_a($jsonType, DaftJson::class, true)) {
            throw new ClassDoesNotImplementClassException(
                $jsonType,
                DaftJson::class
            );
        }
    }

    private static function ArrayToJsonType(
        string $jsonType,
        array $propVal,
        bool $writeAll
    ) : DaftJson {
        /**
        * @var DaftJson $jsonType
        */
        $jsonType = $jsonType;

        return $jsonType::DaftObjectFromJsonArray(
            $propVal,
            $writeAll
        );
    }

    private static function ThrowIfNotDaftJson() : void
    {
        if (false === is_a(static::class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(
                static::class
            );
        }
    }
}
