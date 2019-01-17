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
    * @param array<int|string, scalar|null|array|object> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        TypeUtilities::CheckTypeDefinesOwnIdProperties(static::class, true);
        parent::__construct($data, $writeAll);
    }
}
