<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class DaftObjectNullStubCreatedByArray extends DaftObjectNullStub implements DaftObjectCreatedByArray
{
    /**
    * {@inheritdoc}
    */
    final public function __construct(array $data = [], bool $writeAll = false)
    {
        foreach ($data as $k => $v) {
            if ( ! is_string($k)) {
                throw new InvalidArgumentException(DaftObjectCreatedByArray::ERR_KEY_NOT_STRING);
            }
        }
    }
}
