<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class DaftObjectNullStubCreatedByArray extends DaftObjectNullStub implements DaftObjectCreatedByArray
{
    /**
    * {@inheritdoc}
    */
    final public function __construct(array $data = [], bool $writeAll = false)
    {
    }
}
