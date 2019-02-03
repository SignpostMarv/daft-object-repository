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
    * @tempalte T as class-string
    */
    public static function IsThingStrings(string $maybe, string $thing) : bool
    {
        return is_a($maybe, $thing, true);
    }

    /**
    * @param string|object $object
    *
    * @psalm-param class-string|object $object
    */
    public static function ThrowIfNotType(
        $object,
        int $argument,
        string $class,
        string $function,
        string ...$types
    ) : void {
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
    * @param string|object $object
    *
    * @psalm-param class-string|object $object
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
}
