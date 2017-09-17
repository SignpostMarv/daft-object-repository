<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Base daft object.
*/
interface DaftObjectCreatedByArray extends DaftObject
{
    /**
    * Create an array-backed daft object.
    *
    * @param array $data key-value pairs
    * @param bool $writeAll if TRUE, route $data through static::__set()
    */
    public function __construct(array $data = [], bool $writeAll = false);
}
