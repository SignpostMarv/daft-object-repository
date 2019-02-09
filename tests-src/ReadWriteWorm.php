<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteWorm extends AbstractTestObject implements
    SuitableForRepositoryType,
    DaftObjectWorm
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadWriteWorm>
    */
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;
}
