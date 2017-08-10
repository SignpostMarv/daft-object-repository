<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractArrayBackedDaftObject extends AbstractDaftObject
{
    /**
    * @var array
    */
    private $data = [];

    /**
    * @var bool[]
    */
    private $changedProperties = [];

    public function __construct(array $data = [], bool $writeAll = false)
    {
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

    public function __isset(string $property)
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
    * @return mixed
    */
    protected function RetrieveFromData(string $k)
    {
        return $this->data[$k];
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
