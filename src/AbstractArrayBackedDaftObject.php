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
        if (false === ($this instanceof DaftJson)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(
                static::class
            );
        }

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
        if (false === is_a(static::class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(
                static::class
            );
        }

        $in = [];

        $props = static::DaftObjectJsonProperties();
        $jsonDef = static::DaftObjectJsonProperties();
        $nullableProps = static::DaftObjectNullableProperties();

        $jsonProps = [];

        foreach ($jsonDef as $k => $v) {
            if (is_string($k)) {
                $jsonProps[] = $k;
            } else {
                $jsonProps[] = $v;
            }
        }

        foreach ($jsonProps as $prop) {
            if (
                (
                    false === isset($array[$prop]) ||
                    is_null($array[$prop])
                ) &&
                false === in_array($prop, $nullableProps, true)
            ) {
                throw new PropertyNotNullableException(static::class, $prop);
            }
        }

        foreach (array_keys($array) as $prop) {
            if (
                false === in_array($prop, $props, true) &&
                false === isset($jsonDef[$prop])
            ) {
                throw new UndefinedPropertyException(static::class, $prop);
            } elseif (
                false === in_array($prop, $jsonProps, true)
            ) {
                throw new PropertyNotJsonDecodableException(
                    static::class,
                    $prop
                );
            } elseif (is_null($array[$prop])) {
                continue;
            } elseif (isset($jsonDef[$prop])) {
                $in[$prop] = static::DaftObjectFromJsonType(
                    $prop,
                    $jsonDef[$prop],
                    $array[$prop],
                    $writeAll
                );
            } else {
                $in[$prop] = $array[$prop];
            }
        }

        $out = new static($in, $writeAll);

        if ( ! ($out instanceof DaftJson)) { // here to trick phpstan
            exit;
        }

        return $out;
    }

    /**
    * @param mixed $propVal
    */
    protected static function DaftObjectFromJsonType(
        string $prop,
        string $jsonType,
        $propVal,
        bool $writeAll
    ) {
        $isArray = false;
        if ('[]' === mb_substr($jsonType, -2)) {
            $isArray = true;
            $jsonType = mb_substr($jsonType, 0, -2);
        }

        if ($isArray && false === is_array($propVal)) {
            throw new PropertyNotJsonDecodableShouldBeArrayException(
                static::class,
                $prop
            );
        } elseif (false === is_a($jsonType, DaftJson::class, true)) {
            throw new ClassDoesNotImplementClassException(
                $jsonType,
                DaftJson::class
            );
        }

        /**
        * @var DaftJson $jsonType
        */
        $jsonType = $jsonType;

        if ($isArray) {
            $out = [];

            foreach ($propVal as $i => $val) {
                if (is_array($val)) {
                    $out[] = $jsonType::DaftObjectFromJsonArray(
                        $val,
                        $writeAll
                    );
                } else {
                    throw new PropertyNotJsonDecodableShouldBeArrayException(
                        (string) $jsonType,
                        ($prop . '[' . $i . ']')
                    );
                }
            }

            return $out;
        } elseif (is_array($propVal)) {
            return $jsonType::DaftObjectFromJsonArray(
                $propVal,
                $writeAll
            );
        }

        throw new PropertyNotJsonDecodableShouldBeArrayException(
            (string) $jsonType,
            $prop
        );
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        if (false === is_a(static::class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(
                static::class
            );
        }

        return static::DaftObjectFromJsonArray(json_decode($string, true));
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
                (
                    isset($this->wormProperties[$property]) &&
                    true === $this->wormProperties[$property]
                )
            )
        ) {
            throw new PropertyNotRewriteableException(
                static::class,
                $property
            );
        }

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
}
