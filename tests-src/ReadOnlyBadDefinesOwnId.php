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

    public function __construct(array $data = [], bool $writeAll = false)
    {
        parent::__construct($data, $writeAll);
        self::CheckTypeDefinesOwnIdProperties(static::class, true);
    }
}
