<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyInsuficientIdProperties extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyInsuficientIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    public function GetId() : string
    {
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo'];
    }
}
