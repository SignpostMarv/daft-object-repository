<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnly extends AbstractTestObject implements SuitableForRepositoryType, DefinesOwnStringIdInterface
{
    /**
    * @template-uses DaftObjectIdValuesHashLazyInt<ReadOnly>
    */
    use DaftObjectIdValuesHashLazyInt;
    use DefineIdPropertiesCorrectlyTrait;
    use ReadTrait;
}
