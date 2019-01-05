<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait WriteTrait
{
    public function SetFoo(string $value) : void
    {
        $this->NudgePropertyValue('Foo', $value);
    }

    public function SetBar(float $value) : void
    {
        $this->NudgePropertyValue('Bar', $value);
    }

    public function SetBaz(int $value) : void
    {
        $this->NudgePropertyValue('Baz', $value);
    }

    public function SetBat(? bool $value) : void
    {
        $this->NudgePropertyValue('Bat', $value);
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property property being nudged
    * @param scalar|null|array|object $value value to nudge property with
    *
    * @throws UndefinedPropertyException if $property is not in static::DaftObjectProperties()
    * @throws PropertyNotNullableException if $property is not in static::DaftObjectNullableProperties()
    * @throws PropertyNotRewriteableException if class is write-once read-many and $property was already changed
    */
    abstract protected function NudgePropertyValue(
        string $property,
        $value,
        bool $autoTrimStrings = false,
        bool $throwIfNotUnique = false
    ) : void;
}
