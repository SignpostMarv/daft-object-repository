<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteTwoColumnPrimaryKey extends AbstractTestObject implements SuitableForRepositoryType, DefinesOwnArrayIdInterface
{
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineArrayIdPropertiesCorrectlyTrait;
}
