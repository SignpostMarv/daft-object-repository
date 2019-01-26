<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;
use InvalidArgumentException;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as Base;

class DefinitionAssistant extends Base
{
    /**
    * @var array<string, array<int, string>>
    */
    protected static $types = [];

    public static function IsTypeUnregistered(string $type) : bool
    {
        if ( ! is_a($type, DaftObject::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be an implementation of ' .
                DaftObject::class .
                ', ' .
                $type .
                ' given!'
            );
        }

        return parent::IsTypeUnregistered($type);
    }

    public static function RegisterAbstractDaftObjectType(string $maybe) : void
    {
        if ( ! is_a($maybe, AbstractDaftObject::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be an implementation of ' .
                AbstractDaftObject::class .
                ', ' .
                $maybe .
                ' given!'
            );
        }

        /**
        * @var array<int, string>
        */
        $props = TypeCertainty::EnsureArgumentIsArray($maybe::PROPERTIES);

        static::RegisterDaftObjectTypeFromTypeAndProps($maybe, ...$props);
    }

    protected static function RegisterDaftObjectTypeFromTypeAndProps(
        string $maybe,
        string ...$props
    ) : void {
        $args = static::TypeAndGetterAndSetterClosureWithProps($maybe, ...$props);

        /**
        * @var string
        */
        $type = array_shift($args);

        /**
        * @var Closure|null
        */
        $getter = array_shift($args);

        /**
        * @var Closure|null
        */
        $setter = array_shift($args);

        /**
        * @var array<int, string>
        */
        $props = $args;

        static::RegisterType($type, $getter, $setter, ...$props);
        self::MaybeRegisterAdditionalTypes($type);
    }

    /**
    * @param mixed $maybe
    *
    * @return array<int, string>
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
        /**
        * @var scalar|array|object|resource|null
        */
        $maybe = is_object($maybe) ? get_class($maybe) : $maybe;

        if (is_string($maybe)) {
            if (
                static::IsTypeUnregistered($maybe)
            ) {
                if (
            TypeParanoia::IsThingStrings($maybe, AbstractDaftObject::class)
        ) {
            static::RegisterAbstractDaftObjectType($maybe);
            }
        }

            self::MaybeRegisterAdditionalTypes($maybe);
        }

        return parent::ObtainExpectedProperties($maybe);
    }

    public static function GetterClosure(string $type, string ...$props) : Closure
    {
        return static::SetterOrGetterClosure($type, false, ...$props);
    }

    public static function SetterClosure(string $type, string ...$props) : Closure
    {
        return static::SetterOrGetterClosure($type, false, ...$props);
    }

    public static function TypeAndGetterAndSetterClosureWithProps(
        string $type,
        string ...$props
    ) : array {
        return array_merge(
            [
                $type,
                static::GetterClosure($type, ...$props),
                static::SetterClosure($type, ...$props),
            ],
            $props
        );
    }

    public static function AutoRegisterType(string $type, string ...$properties) : void
    {
        $args = static::TypeAndGetterAndSetterClosureWithProps($type, ...$properties);

        /**
        * @var string
        */
        $type = array_shift($args);

        /**
        * @var Closure|null
        */
        $getter = array_shift($args);

        /**
        * @var Closure|null
        */
        $setter = array_shift($args);

        /**
        * @var array<int, string>
        */
        $properties = $args;

        static::RegisterType($type, $getter, $setter, ...$properties);
    }

    protected static function SetterOrGetterClosure(
        string $type,
        bool $SetNotGet,
        string ...$props
    ) : Closure {
        return function (string $property) use ($type, $props, $SetNotGet) : ? string {
            if (TypeParanoia::MaybeInArray($property, $props)) {
                /**
                * @var string
                */
                $method = TypeUtilities::MethodNameFromProperty($property, $SetNotGet);

                if (method_exists($type, $method)) {
                    return $method;
                }
            }

            return null;
        };
    }

    protected static function MaybeRegisterAdditionalTypes(string $maybe) : void
    {
        foreach (
            [
                DefinesOwnArrayIdInterface::class,
                DefinesOwnIntegerIdInterface::class,
                DefinesOwnStringIdInterface::class,
            ] as $otherType
        ) {
            if ($otherType !== $maybe && self::IsTypeUnregistered($otherType)) {
                self::RegisterDaftObjectTypeFromTypeAndProps($otherType, 'id');
            }
        }
    }
}
