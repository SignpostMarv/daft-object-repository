<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWrite extends AbstractTestObject implements DefinesOwnIdPropertiesInterface
{
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;
}
