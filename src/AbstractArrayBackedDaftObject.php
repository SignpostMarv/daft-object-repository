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
    * List of changes properties.
    *
    * @var bool[]
    */
    private $changedProperties = [];

    /**
    * Create an array-backed daft object.
    *
    * @param array $data key-value pairs
    * @param bool $writeAll if TRUE, route $data through static::__set()
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct();

        if ($writeAll === true) {
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
            $this->changedProperties[$property] === true;
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
        return $this->data[$property];
    }

    /**
    * {@inheritdoc}
    */
    protected function NudgePropertyValue(string $property, $value) : void
    {
        if (in_array($property, static::PROPERTIES, true) !== true) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif (
            is_null($value) === true &&
            in_array($property, static::NULLABLE_PROPERTIES, true) !== true
        ) {
            throw new PropertyNotNullableException(static::class, $property);
        }

        $isChanged = (
            array_key_exists($property, $this->data) === false ||
            $this->data[$property] !== $value
        );

        $this->data[$property] = $value;

        if ($isChanged && isset($this->changedProperties[$property]) !== true) {
            $this->changedProperties[$property] = true;
        }
    }
}
