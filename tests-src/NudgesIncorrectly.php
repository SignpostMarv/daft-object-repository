<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class NudgesIncorrectly extends AbstractTestObject implements SuitableForRepositoryType
{
    /**
    * @template-uses DaftObjectIdValuesHashLazyInt<NudgesIncorrectly>
    */
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;

    public function SetFoo(string $value) : void
    {
        $this->NudgePropertyValue('nope', $value);
    }
}
