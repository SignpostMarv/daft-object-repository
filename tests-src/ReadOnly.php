<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnly extends AbstractTestObject implements SuitableForRepositoryType, DefinesOwnStringIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnly>
    */
    use DaftObjectIdValuesHashLazyInt;
    use DefineIdPropertiesCorrectlyTrait;
    use ReadTrait;
}
