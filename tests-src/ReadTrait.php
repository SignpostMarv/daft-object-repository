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
        $out = $this->RetrievePropertyValueFromData('Bar');

        if (is_string($out) === true && is_numeric($out)) {
            return (float) $out;
        }

        return $out;
    }

    public function GetBaz() : int
    {
        $out = $this->RetrievePropertyValueFromData('Baz');

        if (is_string($out) === true && is_numeric($out)) {
            return (int) $out;
        }

        return $out;
    }

    public function GetBat() : ? bool
    {
        $out = $this->RetrievePropertyValueFromData('Bat');

        if (is_string($out) === true && is_numeric($out)) {
            return (bool) ((int) $out);
        }

        return $out;
    }
}
