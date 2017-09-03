<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyTwoColumnPrimaryKey extends AbstractTestObject implements DefinesOwnArrayIdInterface
{
    use DaftObjectIdValuesHashLazyInt;
    use DefineArrayIdPropertiesCorrectlyTrait;
    use ReadTrait;
}
