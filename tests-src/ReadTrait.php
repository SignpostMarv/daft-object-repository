<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait ReadTrait
{
    public function GetFoo() : string
    {
        return $this->RetrievePropertyValueFromData('Foo');
    }

    public function GetBar() : float
    {
        return $this->RetrievePropertyValueFromData('Bar');
    }

    public function GetBaz() : int
    {
        return $this->RetrievePropertyValueFromData('Baz');
    }

    public function GetBat() : ? bool
    {
        return $this->RetrievePropertyValueFromData('Bat');
    }
}
