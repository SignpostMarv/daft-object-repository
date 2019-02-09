<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class WriteOnlyWorm extends AbstractTestObject implements
    DaftObjectWorm,
    DefinesOwnStringIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<WriteOnlyWorm>
    */
    use DaftObjectIdValuesHashLazyInt;
    use WriteTrait;

    public function GetId() : string
    {
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo'];
    }
}
