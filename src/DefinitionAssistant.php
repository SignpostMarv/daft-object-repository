<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;
use InvalidArgumentException;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as Base;

/**
* @template-extends Base<DaftObject>
*/
class DefinitionAssistant extends Base
{
    const BOOL_EXPECTING_GETTER = false;

    const BOOL_EXPECTING_SETTER = true;

    const INT_ARRAY_INDEX_TYPE = 0;

    const INT_ARRAY_INDEX_GETTER = 1;

    const INT_ARRAY_INDEX_SETTER = 2;

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

    /**
    * @psalm-param class-string<AbstractDaftObject> $maybe
    */
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

    public static function ObtainExpectedProperties($maybe) : array
    {
        $maybe = is_object($maybe) ? get_class($maybe) : $maybe;

        if (static::IsTypeUnregistered($maybe)) {
            if (TypeParanoia::IsThingStrings($maybe, AbstractDaftObject::class)) {
                static::RegisterAbstractDaftObjectType($maybe);
            }
        }

        self::MaybeRegisterAdditionalTypes($maybe);

        return parent::ObtainExpectedProperties($maybe);
    }

    /**
    * @return Closure(string):?string
    */
    public static function GetterClosure(string $type, string ...$props) : Closure
    {
        return static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_GETTER, ...$props);
    }

    /**
    * @return Closure(string):?string
    */
    public static function SetterClosure(string $type, string ...$props) : Closure
    {
        return static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_SETTER, ...$props);
    }

    /**
    * @psalm-param class-string<DaftObject> $type
    *
    * @psalm-return array{0:class-string<DaftObject>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
    */
    public static function TypeAndGetterAndSetterClosureWithProps(
        string $type,
        string ...$props
    ) : array {
        /**
        * @psalm-var array{0:class-string<DaftObject>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
        */
        $out = array_merge(
            [
                $type,
                static::GetterClosure($type, ...$props),
                static::SetterClosure($type, ...$props),
            ],
            $props
        );

        return $out;
    }

    /**
    * @psalm-param class-string<DaftObject> $type
    */
    public static function AutoRegisterType(string $type, string ...$properties) : void
    {
        static::RegisterDaftObjectTypeFromTypeAndProps($type, ...$properties);
    }

    /**
    * @psalm-param class-string<DaftObject> $maybe
    */
    protected static function RegisterDaftObjectTypeFromTypeAndProps(
        string $maybe,
        string ...$props
    ) : void {
        $args = static::TypeAndGetterAndSetterClosureWithProps($maybe, ...$props);

        /**
        * @var array<int, string>
        */
        $props = array_slice($args, 3);

        static::RegisterType(
            $args[self::INT_ARRAY_INDEX_TYPE],
            $args[self::INT_ARRAY_INDEX_GETTER],
            $args[self::INT_ARRAY_INDEX_SETTER],
            ...$props
        );
        self::MaybeRegisterAdditionalTypes($args[0]);
    }

    /**
    * @psalm-return Closure(string):?string
    */
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
