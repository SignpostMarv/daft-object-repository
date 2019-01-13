<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class TypeParanoia extends TypeCertainty
{
    const INDEX_SECOND_ARG = 2;

    const INT_ARG_OFFSET = 5;

    /**
    * @param mixed $needle
    * @param mixed $haystack
    */
    public static function MaybeInMaybeArray($needle, $haystack) : bool
    {
        $haystack = self::EnsureArgumentIsArray($haystack, self::INDEX_SECOND_ARG, __METHOD__);

        return static::MaybeInArray($needle, $haystack);
    }

    /**
    * @param mixed $needle
    */
    public static function MaybeInArray($needle, array $haystack) : bool
    {
        return in_array($needle, $haystack, true);
    }

    public static function IsThingStrings(string $maybe, string $thing) : bool
    {
        return is_a($maybe, $thing, true);
    }

    public static function IsSubThingStrings(string $maybe, string $thing) : bool
    {
        return is_subclass_of($maybe, $thing, true);
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
    * @param mixed $object
    */
    public static function ThrowIfNotType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
        if ( ! is_object($object) && ! is_string($object)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an object or a string!'
            );
        }

        foreach ($types as $i => $type) {
            if ( ! interface_exists($type) && ! class_exists($type)) {
                throw new InvalidArgumentException(
                    'Argument ' .
                    (self::INT_ARG_OFFSET + $i) .
                    ' passed to ' .
                    __METHOD__ .
                    ' must be a class or interface!'
                );
            } elseif ( ! is_a($object, $type, is_string($object))) {
                throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                    $argument,
                    $class,
                    $function,
                    $type,
                    is_string($object) ? $object : get_class($object)
                );
            }
        }
    }

    /**
    * @param mixed $object
    */
    public static function ThrowIfNotDaftObjectType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
        static::ThrowIfNotType(
            $object,
            $argument,
            $class,
            $function,
            DaftObject::class,
            ...$types
        );
    }

    /**
    * @param mixed $object
    */
    public static function ThrowIfNotDaftObjectIdPropertiesType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
        static::ThrowIfNotDaftObjectType(
            $object,
            $argument,
            $class,
            $function,
            DefinesOwnIdPropertiesInterface::class,
            ...$types
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

                return TypeParanoia::MaybeInArray(gettype($maybe), $types);
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
        if (TypeParanoia::MaybeInArray('string', $types) && $autoTrimStrings) {
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
