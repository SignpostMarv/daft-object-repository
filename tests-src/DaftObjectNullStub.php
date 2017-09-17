<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class DaftObjectNullStub extends AbstractDaftObject
{
    /**
    * {@inheritdoc}
    */
    final public function __isset(string $property) : bool
    {
        return false;
    }

    /**
    * {@inheritdoc}
    */
    final public function ChangedProperties() : array
    {
        return [];
    }

    /**
    * {@inheritdoc}
    */
    final public function MakePropertiesUnchanged(string ...$properties) : void
    {
    }

    /**
    * {@inheritdoc}
    */
    final public function HasPropertyChanged(string $property) : bool
    {
        return false;
    }

    /**
    * {@inheritdoc}
    */
    final protected function NudgePropertyValue(string $property, $value) : void
    {
    }
}
