<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use ReflectionException;
use ReflectionMethod;

class TypeUtilities
{
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
        $method = TypeUtilities::MethodNameFromProperty($property, $SetNotGet);

        try {
            $ref = new ReflectionMethod($class, $method);

            return ($pub ? $ref->isPublic() : $ref->isProtected()) && false === $ref->isStatic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public static function MethodNameFromProperty(string $prop, bool $SetNotGet = false) : string
    {
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

    final protected static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        /**
        * @var string[] $props
        */
        $props = $class::DaftObjectProperties();

        foreach ($props as $prop) {
            if (TypeUtilities::HasMethod($class, $prop, false)) {
                self::$Getters[$class][$prop] = true;
            } elseif (TypeUtilities::HasMethod($class, $prop, false, false)) {
                self::$Getters[$class][$prop] = false;
            }

            if (TypeUtilities::HasMethod($class, $prop, true)) {
                self::$publicSetters[$class][] = $prop;
            }
        }
    }

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
