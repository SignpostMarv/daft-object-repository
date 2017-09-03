<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DefineArrayIdPropertiesCorrectlyTrait
{
    public function GetId() : array
    {
        return [
            $this->GetFoo(),
            $this->GetBar(),
        ];
    }

    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
            'Bar',
        ];
    }
}
