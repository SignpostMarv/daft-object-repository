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
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public function GetBar() : float
    {
        /**
        * @var float|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Bar');

        if (is_string($out)) {
            return (float) $out;
        }

        return $out;
    }

    public function GetBaz() : int
    {
        /**
        * @var int|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Baz');

        if (is_string($out)) {
            return (int) $out;
        }

        return $out;
    }

    public function GetBat() : ? bool
    {
        /**
        * @var bool|null|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Bat');

        if (is_string($out)) {
            return (bool) ((int) $out);
        }

        return $out;
    }
}
