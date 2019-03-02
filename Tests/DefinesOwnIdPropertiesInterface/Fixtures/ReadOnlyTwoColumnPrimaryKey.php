<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DefinesOwnIdPropertiesInterface\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @property-read string $Foo
* @property-read float $Bar
* @property-read bool|null $Bat
* @property-read int $Baz
* @property-read scalar[] $id
*/
class ReadOnlyTwoColumnPrimaryKey extends AbstractArrayBackedDaftObject implements DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyTwoColumnPrimaryKey>
    */
    use DaftObjectIdValuesHashLazyInt;

    /**
    * @return scalar[]
    */
    public function GetId() : array
    {
        return [
            $this->GetFoo(),
            $this->GetBar(),
        ];
    }

    /**
    * @return array<int, string>
    */
    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
            'Bar',
        ];
    }

    public function GetFoo() : string
    {
        return TypeUtilities::ExpectRetrievedValueIsString(
            'Foo',
            $this->RetrievePropertyValueFromData('Foo'),
            static::class
        );
    }

    public function GetBar() : float
    {
        return TypeUtilities::ExpectRetrievedValueIsFloatish(
            'Bar',
            $this->RetrievePropertyValueFromData('Bar'),
            static::class
        );
    }

    public function GetBaz() : int
    {
        return TypeUtilities::ExpectRetrievedValueIsIntish(
            'Baz',
            $this->RetrievePropertyValueFromData('Baz'),
            static::class
        );
    }

    public function GetBat() : ? bool
    {
        /**
        * @var bool|null|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Bat');

        return is_string($out) ? ((bool) ((int) $out)) : $out;
    }
}
