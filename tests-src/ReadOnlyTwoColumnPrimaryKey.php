<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyTwoColumnPrimaryKey extends AbstractTestObject implements DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyTwoColumnPrimaryKey>
    */
    use DaftObjectIdValuesHashLazyInt;
    use DefineArrayIdPropertiesCorrectlyTrait;
    use ReadTrait;
}
