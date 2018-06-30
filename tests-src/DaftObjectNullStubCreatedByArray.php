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
        $keys = array_keys($data);
        $notString = array_filter($keys, 'is_string');

        if (count($keys) !== count($notString)) {
                throw new InvalidArgumentException(DaftObjectCreatedByArray::ERR_KEY_NOT_STRING);
        }
    }
}
