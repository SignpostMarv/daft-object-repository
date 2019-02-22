<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWrite extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadWrite>
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
