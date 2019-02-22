<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\SuitableForRepositoryType\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @property-read int $id
* @property-read string $foo
*/
class SuitableForRepositoryIntType extends AbstractArrayBackedDaftObject implements
    SuitableForRepositoryType,
    DefinesOwnIntegerIdInterface
{
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = [
        'id',
        'foo',
    ];

    public function GetId() : int
    {
        return (int) $this->RetrievePropertyValueFromData('id');
    }

    public function GetFoo() : string
    {
        return (string) $this->RetrievePropertyValueFromData('foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['id'];
    }
}
