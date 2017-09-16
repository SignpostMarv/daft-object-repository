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
abstract class AbstractArrayBackedDaftObject extends AbstractDaftObject
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
    * Create an array-backed daft object.
    *
    * @param array $data key-value pairs
    * @param bool $writeAll if TRUE, route $data through static::__set()
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
    * Retrieve a property from data.
    *
    * @param string $property the property being retrieved
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
