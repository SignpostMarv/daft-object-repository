<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DefineIdPropertiesInsufficientlyTrait
{
    /**
    * @return null
    */
    public function GetId()
    {
        return null;
    }

    /**
    * @return string[]
    */
    public static function DaftObjectIdProperties() : array
    {
        return [];
    }
}
