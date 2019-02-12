<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyBadDefinesOwnId extends AbstractTestObject
{
    use DefineIdPropertiesIncorrectlyTrait;
    use ReadTrait;

    /**
    * @param array<string, scalar|array|object|null> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct($data, $writeAll);
    }
}
