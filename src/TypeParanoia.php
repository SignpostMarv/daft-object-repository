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
    public static function EnsureArgumentIsString($maybe) : string
    {
        if ( ! is_string($maybe)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be a string, ' .
                (is_object($maybe) ? get_class($maybe) : gettype($maybe))
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
}
