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
    const ERR_KEY_NOT_STRING = 'Properties must be strings!';

    /**
    * Create an array-backed daft object.
    *
    * @param array<int|string, scalar|array|object|null> $data key-value pairs
    * @param bool $writeAll if TRUE, route $data through static::__set()
    */
    public function __construct(array $data = [], bool $writeAll = false);
}
