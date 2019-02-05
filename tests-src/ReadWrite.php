<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWrite extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @template-uses DaftObjectIdValuesHashLazyInt<ReadWrite>
    */
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;
}
