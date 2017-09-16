<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteWorm extends AbstractTestObject implements
    DefinesOwnIdPropertiesInterface,
    DaftObjectWorm
{
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;
}
