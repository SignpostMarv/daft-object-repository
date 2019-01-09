<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class TypeParanoia
{
    const INDEX_FIRST_ARG = 1;

    const INDEX_SECOND_ARG = 2;

    const BOOL_VAR_EXPORT_RETURN = true;

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

    /**
    * @param mixed $maybe
    */
    public static function EnsureArgumentIsArray($maybe, int $argument = null, string $method = __METHOD__) : array
    {
        if ( ! is_array($maybe)) {
            throw new InvalidArgumentException(
                'Argument ' .
                (is_int($argument) ? $argument : self::INDEX_FIRST_ARG) .
                ' passed to ' .
                $method .
                ' must be an array, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe)) .
                ' given!'
            );
        }

        return $maybe;
    }

    /**
    * @param mixed $maybe
    */
    public static function ForceArgumentAsArray($maybe) : array
    {
        return is_array($maybe) ? $maybe : [$maybe];
    }

    /**
    * @param mixed $maybe
    */
    public static function VarExportNonScalars($maybe) : string
    {
        if (is_string($maybe)) {
            return $maybe;
        }

        return
            is_scalar($maybe)
                ? (string) $maybe
                : var_export($maybe, self::BOOL_VAR_EXPORT_RETURN);
    }

    /**
    * @param mixed $maybe
    */
    public static function EnsureArgumentIsString($maybe) : string
    {
        if ( ! is_string($maybe)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be a string, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe)) .
                ' given!'
            );
        }

        return $maybe;
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
