<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyInsuficientIdProperties extends AbstractTestObject implements DefinesOwnIdPropertiesInterface
{
    use DaftObjectIdValuesHashLazyInt;
    use DefineIdPropertiesInsufficientlyTrait;
    use ReadTrait;
}
