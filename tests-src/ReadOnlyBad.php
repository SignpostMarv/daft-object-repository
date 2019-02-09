<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyBad extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyBad>
    */
    use DaftObjectIdValuesHashLazyInt;
    use DefineIdPropertiesIncorrectlyTrait;
    use ReadTrait;
}
