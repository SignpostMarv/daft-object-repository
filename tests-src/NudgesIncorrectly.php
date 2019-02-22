<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class NudgesIncorrectly extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<NudgesIncorrectly>
    */
    use DaftObjectIdValuesHashLazyInt;

    public function SetFoo(string $value) : void
    {
        $this->NudgePropertyValue('nope', $value);
    }

    public function GetId() : string
    {
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo'];
    }
}
