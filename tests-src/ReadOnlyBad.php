<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnlyBad extends AbstractTestObject implements DefinesOwnIdPropertiesInterface
{
    use DefineIdPropertiesIncorrectlyTrait;
    use ReadTrait;
}
