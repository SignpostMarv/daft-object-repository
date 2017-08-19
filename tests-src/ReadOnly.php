<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadOnly extends AbstractTestObject implements DefinesOwnStringIdInterface
{
    use DefineIdPropertiesCorrectlyTrait;
    use ReadTrait;
}
