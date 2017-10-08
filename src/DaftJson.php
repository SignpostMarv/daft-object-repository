<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use JsonSerializable;

/**
* Base daft object.
*/
interface DaftJson extends DaftObject, JsonSerializable
{
    /**
    * @return string[]
    */
    public static function DaftObjectJsonProperties() : array;

    public static function DaftObjectFromJsonArray(
        array $array,
        bool $writeAll = false
    ) : DaftJson;

    public static function DaftObjectFromJsonString(string $string) : DaftJson;
}
