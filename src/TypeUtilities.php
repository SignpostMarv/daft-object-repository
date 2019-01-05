<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;

class TypeUtilities
{
    const SUPPORTED_INVALID_LEADING_CHARACTERS = [
        '@',
    ];

    /**
    * @var array<string, array<string, bool>>
    */
    private static $Getters = [];

    /**
    * @var array<string, array<int, string>>
    */
    private static $publicSetters = [];

    public static function DaftObjectPublicGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(array_filter(self::$Getters[$class]));
    }

    public static function DaftObjectPublicOrProtectedGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(self::$Getters[$class]);
    }

    public static function DaftObjectPublicSetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicSetters[$class];
    }

    public static function HasMethod(
        string $class,
        string $property,
        bool $SetNotGet,
        bool $pub = true
    ) : bool {
        $method = static::MethodNameFromProperty($property, $SetNotGet);

        try {
            $ref = new ReflectionMethod($class, $method);

            return ($pub ? $ref->isPublic() : $ref->isProtected()) && false === $ref->isStatic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public static function MethodNameFromProperty(string $prop, bool $SetNotGet = false) : string
    {
        if (in_array(mb_substr($prop, 0, 1), self::SUPPORTED_INVALID_LEADING_CHARACTERS, true)) {
            return ($SetNotGet ? 'Alter' : 'Obtain') . ucfirst(mb_substr($prop, 1));
        }

        return ($SetNotGet ? 'Set' : 'Get') . ucfirst($prop);
    }

    /**
    * Checks if a type correctly defines it's own id.
    *
    * @throws ClassDoesNotImplementClassException if $class is not an implementation of DefinesOwnIdPropertiesInterface
    * @throws ClassMethodReturnHasZeroArrayCountException if $class::DaftObjectIdProperties() does not contain at least one property
    * @throws ClassMethodReturnIsNotArrayOfStringsException if $class::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $class::DaftObjectIdProperties()
    */
    public static function CheckTypeDefinesOwnIdProperties(
        string $class,
        bool $throwIfNotImplementation = false
    ) : void {
        if (is_a($class, DefinesOwnIdPropertiesInterface::class, true)) {
            self::CheckTypeDefinesOwnIdPropertiesIsImplementation($class);
        } elseif ($throwIfNotImplementation) {
            throw new ClassDoesNotImplementClassException(
                $class,
                DefinesOwnIdPropertiesInterface::class
            );
        }
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
    protected static function MaybeThrowIfNotArrayIntKeys(array $value) : array
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
    * @return array<int, mixed> filtered $value
    */
    protected static function MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        array $value,
        string ...$types
    ) : array {
        $value = static::MaybeThrowIfNotArrayIntKeys($value);

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

                return in_array(gettype($maybe), $types, true);
            }
        );

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained values that did not match the provided types!'
            );
        }

        $initialCount = count($value);

        if (in_array('string', $types, true) && $autoTrimStrings && $initialCount > 0) {
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

    final protected static function CachePublicGettersAndSetters(string $class) : void
    {
        if (false === isset(self::$Getters[$class])) {
            self::$Getters[$class] = [];
            self::$publicSetters[$class] = [];

            if (is_a($class, DefinesOwnIdPropertiesInterface::class, true)) {
                self::$Getters[$class]['id'] = true;
            }

            self::CachePublicGettersAndSettersProperties($class);
        }
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    final protected static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        /**
        * @var string[]
        */
        $props = $class::DaftObjectProperties();

        foreach ($props as $prop) {
            if (static::HasMethod($class, $prop, false)) {
                self::$Getters[$class][$prop] = true;
            } elseif (static::HasMethod($class, $prop, false, false)) {
                self::$Getters[$class][$prop] = false;
            }

            if (static::HasMethod($class, $prop, true)) {
                self::$publicSetters[$class][] = $prop;
            }
        }
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    final protected static function CheckTypeDefinesOwnIdPropertiesIsImplementation(
        string $class
    ) : void {
        $properties = (array) $class::DaftObjectIdProperties();

        if (count($properties) < 1) {
            throw new ClassMethodReturnHasZeroArrayCountException(
                $class,
                'DaftObjectIdProperties'
            );
        } elseif (count($properties) !== count(array_filter($properties, 'is_string'))) {
            throw new ClassMethodReturnIsNotArrayOfStringsException(
                $class,
                'DaftObjectIdProperties'
            );
        }
    }
}
