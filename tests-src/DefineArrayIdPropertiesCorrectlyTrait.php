<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DefineArrayIdPropertiesCorrectlyTrait
{
    /**
    * @return scalar[]
    */
    public function GetId() : array
    {
        return [
            $this->GetFoo(),
            $this->GetBar(),
        ];
    }

    /**
    * @return array<int, string>
    */
    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
            'Bar',
        ];
    }
}
