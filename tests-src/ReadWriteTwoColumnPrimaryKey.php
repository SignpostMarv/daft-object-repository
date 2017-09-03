<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteTwoColumnPrimaryKey extends AbstractTestObject implements DefinesOwnArrayIdInterface
{
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineArrayIdPropertiesCorrectlyTrait;
}
