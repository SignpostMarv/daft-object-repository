<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DefineIdPropertiesCorrectlyTrait
{
    public function GetId() : string
    {
        return $this->GetFoo();
    }

    /**
    * @return array<int, string>
    */
    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
        ];
    }
}
