<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteTwoColumnPrimaryKey extends AbstractTestObject implements SuitableForRepositoryType, DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadWriteTwoColumnPrimaryKey>
    */
    use DaftObjectIdValuesHashLazyInt;

    public function GetId() : array
    {
        return (array) [
            $this->GetFoo(),
            $this->GetBar(),
        ];
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo', 'Bar'];
    }
}
