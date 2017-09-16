<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class WriteOnlyWorm extends AbstractTestObject implements DaftObjectWorm
{
    use DaftObjectIdValuesHashLazyInt;
    use WriteTrait;
}
