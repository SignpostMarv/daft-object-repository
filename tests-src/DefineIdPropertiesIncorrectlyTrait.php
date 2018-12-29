<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DefineIdPropertiesIncorrectlyTrait
{
    /**
    * @return null
    */
    public function GetId()
    {
        return null;
    }

    public static function DaftObjectIdProperties() : array
    {
        return [1];
    }
}
