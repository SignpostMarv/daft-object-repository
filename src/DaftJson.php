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
*
* @template T as DaftJson
*/
interface DaftJson extends DaftObject, JsonSerializable
{
    /**
    * @return array<string|int, string>
    */
    public static function DaftObjectJsonProperties() : array;

    /**
    * @return array<int, string>
    */
    public static function DaftObjectJsonPropertyNames() : array;

    /**
    * @return static
    *
    * @psalm-return T
    */
    public static function DaftObjectFromJsonArray(array $array, bool $writeAll = false) : self;

    /**
    * @return static
    *
    * @psalm-return T
    */
    public static function DaftObjectFromJsonString(string $string) : self;
}
