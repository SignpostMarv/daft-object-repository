<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait WriteTrait
{
    public function SetFoo(string $value)
    {
        return $this->NudgePropertyValue('Foo', $value);
    }

    public function SetFooNotNullable(? string $value)
    {
        return $this->NudgePropertyValue('Foo', $value);
    }

    public function SetBar(float $value)
    {
        return $this->NudgePropertyValue('Bar', $value);
    }

    public function SetBarUndefined(float $value)
    {
        return $this->NudgePropertyValue('BarUndefined', $value);
    }

    public function SetBaz(int $value)
    {
        return $this->NudgePropertyValue('Baz', $value);
    }

    public function SetBat(? bool $value)
    {
        return $this->NudgePropertyValue('Bat', $value);
    }
}
