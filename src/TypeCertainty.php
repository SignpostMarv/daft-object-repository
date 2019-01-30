<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class TypeCertainty
{
    const INDEX_FIRST_ARG = 1;

    const BOOL_VAR_EXPORT_RETURN = true;

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
    *
    * @template T
    *
    * @psalm-param T|T[] $maybe
    *
    * @psalm-return T[]
    */
    public static function ForceArgumentAsArray($maybe) : array
    {
        if (is_array($maybe)) {
            /**
            * @psalm-var T[]
            */
            $maybe = $maybe;

            return $maybe;
        }

        return [$maybe];
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

    /**
    * @param mixed $maybe
    *
    * @return object|string
    */
    public static function EnsureArgumentIsObjectOrString($maybe, int $argument, string $method)
    {
        if ( ! is_object($maybe) && ! is_string($maybe)) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                ' must be an object or a string!'
            );
        }

        return $maybe;
    }
}
