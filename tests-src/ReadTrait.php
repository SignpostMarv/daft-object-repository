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
        return $this->RetrieveFromData('Foo');
    }

    public function GetBar() : float
    {
        return $this->RetrieveFromData('Bar');
    }

    public function GetBaz() : int
    {
        return $this->RetrieveFromData('Baz');
    }

    public function GetBat() : ? bool
    {
        return $this->RetrieveFromData('Bat');
    }
}
