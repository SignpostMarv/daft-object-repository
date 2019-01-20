<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class DefinitionAssistant
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
                '() must be an instance of ' .
                DaftObject::class .
                ', ' .
                $type .
                ' given!'
            );
        }

        return ! isset(self::$types[$type]);
    }

    public static function RegisterType(string $type, array $matrix) : void
    {
        if ( ! static::IsTypeUnregistered($type)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() has already been registered!'
            );
        }

        $initialCount = count($matrix);

        if ($initialCount < 1) {
            throw new InvalidArgumentException(
                'Argument 2 passed to ' .
                __METHOD__ .
                '() must be a non-empty array!'
            );
        }

        /**
        * @var array<int, mixed>
        */
        $matrix = array_filter($matrix, 'is_int', ARRAY_FILTER_USE_KEY);

        if (count($matrix) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 2 passed to ' .
                __METHOD__ .
                '() must be an array with only integer keys!'
            );
        }

        /**
        * @var array<int, string>
        */
        $matrix = array_filter($matrix, 'is_string');

        if (count($matrix) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 2 passed to ' .
                __METHOD__ .
                '() must be an array of shape array<int, string>!'
            );
        }

        self::$types[$type] = $matrix;
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
        } elseif ( ! static::IsTypeUnregistered($maybe)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() has already been registered!'
            );
        }

        self::$types[$maybe] = TypeCertainty::EnsureArgumentIsArray($maybe::PROPERTIES);
    }

    /**
    * @param scalar|object $maybe
    *
    * @return array<int, string>
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
        if (is_object($maybe)) {
            if (! ($maybe instanceof DaftObject)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be either a string or an instance of ' .
                DaftObject::class .
                ', ' .
                get_class($maybe) .
                ' given!'
            );
            }
        } elseif ( ! is_string($maybe)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be either a string or an object, ' .
                gettype($maybe) .
                ' given!'
            );
        }

        /**
        * @var array<int, string>
        */
        $out = array_values(array_unique(array_reduce(
            array_filter(
                self::$types,
                function (string $type) use ($maybe) : bool {
                    return is_a($maybe, $type, is_string($maybe));
                },
                ARRAY_FILTER_USE_KEY
            ),
            'array_merge',
            []
        )));

        return $out;
    }

    /**
    * @return array<int, string>
    */
    public static function ObtainExpectedOrDefaultPropertiesWithAutoRegister(string $class) : array
    {
        if (
            static::IsTypeUnregistered($class) &&
            TypeParanoia::IsThingStrings($class, AbstractDaftObject::class)
        ) {
            static::RegisterAbstractDaftObjectType($class);
        }

        /**
        * @var array<int, string>
        */
        $out =
            ! static::IsTypeUnregistered($class)
                ? static::ObtainExpectedProperties($class)
                : $class::DaftObjectProperties();

        return $out;
    }
}
