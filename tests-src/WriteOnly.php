<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class WriteOnly extends AbstractTestObject implements DefinesOwnStringIdInterface
{
    /**
    * @template-uses DaftObjectIdValuesHashLazyInt<WriteOnly>
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
