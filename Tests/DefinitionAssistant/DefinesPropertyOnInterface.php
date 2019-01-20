<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use SignpostMarv\DaftObject\DaftObject;

interface DefinesPropertyOnInterface extends DaftObject
{
    const DefinitionAssistantProperties = [
        'foo',
    ];

    public function GetFoo() : string;

    public function SetFoo(string $value) : void;
}
