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
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @property-read int $id
* @property string $foo
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

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    public function GetId() : int
    {
        return TypeUtilities::ExpectRetrievedValueIsIntish(
            'id',
            $this->RetrievePropertyValueFromData(
                'id'
            ),
            static::class
        );
    }

    public function GetFoo() : string
    {
        return TypeUtilities::ExpectRetrievedValueIsString(
            'foo',
            $this->RetrievePropertyValueFromData(
                'foo'
            ),
            static::class
        );
    }

    public function SetFoo(string $value) : void
    {
        $this->NudgePropertyValue('foo', $value);
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['id'];
    }
}
