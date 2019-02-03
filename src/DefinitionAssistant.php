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

    const IS_A_STRINGS = true;

    /**
    * @psalm-param class-string<DaftObject> $type
    */
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
        /**
        * @var array<int, string>
        */
        $props = $maybe::PROPERTIES;

        static::RegisterDaftObjectTypeFromTypeAndProps($maybe, ...$props);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param class-string<DaftObject>|DaftObject $maybe
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
        /**
        * @psalm-var class-string<DaftObject>
        */
        $maybe = is_object($maybe) ? get_class($maybe) : $maybe;

        if (static::IsTypeUnregistered($maybe)) {
            if (is_a($maybe, AbstractDaftObject::class, true)) {
                static::RegisterAbstractDaftObjectType($maybe);
            }
        }

        $maybe = self::MaybeRegisterAdditionalTypes($maybe);

        return parent::ObtainExpectedProperties($maybe);
    }

    /**
    * @psalm-param class-string<DaftObject> $type
    */
    public static function AutoRegisterType(string $type, string ...$properties) : void
    {
        static::RegisterDaftObjectTypeFromTypeAndProps($type, ...$properties);
    }

    /**
    * @psalm-return Closure(string):?string
    */
    public static function SetterOrGetterClosure(
        string $type,
        bool $SetNotGet,
        string ...$props
    ) : Closure {
        return function (string $property) use ($type, $props, $SetNotGet) : ? string {
            if (in_array($property, $props, self::IN_ARRAY_STRICT_MODE)) {
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

    /**
    * @param mixed $value
    *
    * @return array<int, mixed> filtered $value
    */
    public static function MaybeThrowIfValueDoesNotMatchMultiTypedArray(
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        $value,
        string ...$types
    ) : array {
        if ( ! is_array($value)) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' must be an array, ' .
                (is_object($value) ? get_class($value) : gettype($value)) .
                ' given!'
            );
        }

        return static::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
            $autoTrimStrings,
            $throwIfNotUnique,
            $value,
            ...$types
        );
    }

    /**
    * @psalm-param class-string<DaftObject> $type
    *
    * @psalm-return array{0:class-string<DaftObject>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
    */
    private static function TypeAndGetterAndSetterClosureWithProps(
        string $type,
        string ...$props
    ) : array {
        /**
        * @psalm-var array{0:class-string<DaftObject>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
        */
        $out = array_merge(
            [
                $type,
                static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_GETTER, ...$props),
                static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_SETTER, ...$props),
            ],
            $props
        );

        return $out;
    }

    /**
    * @psalm-param class-string<DaftObject> $maybe
    *
    * @psalm-return class-string<DaftObject>
    */
    private static function RegisterDaftObjectTypeFromTypeAndProps(
        string $maybe,
        string ...$props
    ) : string {
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

        return self::MaybeRegisterAdditionalTypes($args[self::INT_ARRAY_INDEX_TYPE]);
    }

    /**
    * @psalm-param class-string<DaftObject> $maybe
    *
    * @psalm-return class-string<DaftObject>
    */
    private static function MaybeRegisterAdditionalTypes(string $maybe) : string
    {
        return array_reduce(
            array_filter(
                [
                    DefinesOwnArrayIdInterface::class,
                    DefinesOwnIntegerIdInterface::class,
                    DefinesOwnStringIdInterface::class,
                ],
                function (string $otherType) use ($maybe) : bool {
                    return $otherType !== $maybe;
                }
            ),
            /**
            * @psalm-param class-string<DaftObject> $maybe
            * @psolm-param class-string<DaftObject> $otherType
            */
            function (string $maybe, string $otherType) : string {
                /**
                * @psalm-var class-string<DaftObject>
                */
                $otherType = $otherType;

                if (self::IsTypeUnregistered($otherType)) {
                    self::RegisterDaftObjectTypeFromTypeAndProps($otherType, 'id');
                }

                return $maybe;
            },
            $maybe
        );
    }

    /**
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        array $value,
        string ...$types
    ) : array {
        $value = static::MaybeThrowIfNotArrayIntKeys($value);
        $value = static::MaybeThrowIfValueArrayDoesNotMatchTypes($value, ...$types);
        $value = static::MaybeRemapStringsToTrimmedStrings($value, $autoTrimStrings, ...$types);

        $initialCount = count($value);

        $value = array_unique($value, SORT_REGULAR);

        if ($throwIfNotUnique && count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained non-unique values!'
            );
        }

        return array_values($value);
    }

    /**
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfNotArrayIntKeys(array $value) : array
    {
        $initialCount = count($value);

        /**
        * @var array<int, mixed>
        */
        $value = array_filter($value, 'is_int', ARRAY_FILTER_USE_KEY);

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' must be array<int, mixed>'
            );
        }

        return $value;
    }

    /**
    * @param array<int, mixed> $value
    *
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfValueArrayDoesNotMatchTypes(
        array $value,
        string ...$types
    ) : array {
        $initialCount = count($value);

        $value = array_filter(
            $value,
            /**
            * @param mixed $maybe
            */
            function ($maybe) use ($types) : bool {
                if (is_object($maybe)) {
                    foreach ($types as $maybeType) {
                        if (is_a($maybe, $maybeType)) {
                            return true;
                        }
                    }

                    return false;
                }

                return in_array(
                    gettype($maybe),
                    $types,
                    DefinitionAssistant::IN_ARRAY_STRICT_MODE
                );
            }
        );

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained values that did not match the provided types!'
            );
        }

        return $value;
    }

    /**
    * @param array<int, mixed> $value
    *
    * @return array<int, mixed>
    */
    private static function MaybeRemapStringsToTrimmedStrings(
        array $value,
        bool $autoTrimStrings,
        string ...$types
    ) : array {
        if (
            $autoTrimStrings &&
            in_array('string', $types, DefinitionAssistant::IN_ARRAY_STRICT_MODE)
        ) {
            $value = array_map(
                /**
                * @param mixed $maybe
                *
                * @return mixed
                */
                function ($maybe) {
                    return is_string($maybe) ? trim($maybe) : $maybe;
                },
                $value
            );
        }

        return $value;
    }
}
