<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyBad extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyBad>
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
