<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use Closure;
use SignpostMarv\DaftObject\DefinitionAssistant as Base;

class DefinitionAssistant extends Base
{
    public static function ClearTypes() : void
    {
        static::$properties = [];
        static::$getters = [];
        static::$setters = [];
    }

    public static function PublicSetterOrGetterClosure(
        string $type,
        bool $SetNotGet,
        string ...$props
    ) : Closure {
        return static::SetterOrGetterClosure($type, $SetNotGet, ...$props);
    }
}
