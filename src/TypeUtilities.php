<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;
use ReflectionException;
use ReflectionMethod;

class TypeUtilities
{
    /**
    * @var array<string, array<int, string>>
    */
    private static $publicGetters = [];

    /**
    * @var array<string, array<int, string>>
    */
    private static $publicSetters = [];

    public static function DaftObjectPublicGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicGetters[$class];
    }

    public static function DaftObjectPublicSetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicSetters[$class];
    }

    public static function ThrowIfNotDaftJson(string $class) : void
    {
        if (false === is_a($class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException($class);
        }
    }

    public static function HasPublicMethod(string $class, string $property, bool $SetNotGet) : bool
    {
        $method = TypeUtilities::MethodNameFromProperty($property, $SetNotGet);

        try {
            $mRef = new ReflectionMethod($class, $method);

            return $mRef->isPublic() && false === $mRef->isStatic();
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

    public static function ThrowIfNotJsonType(string $jsonType) : void
    {
        if (false === is_a($jsonType, DaftJson::class, true)) {
            throw new ClassDoesNotImplementClassException($jsonType, DaftJson::class);
        }
    }

    public static function MakeMapperThrowIfJsonDefNotValid(
        string $class,
        array $jsonDef,
        array $array
    ) : Closure {
        $mapper =
            /**
            * @return mixed
            */
            function (string $prop) use ($jsonDef, $array, $class) {
                if (isset($jsonDef[$prop]) && false === is_array($array[$prop])) {
                    static::ThrowBecauseArrayJsonTypeNotValid($class, $jsonDef[$prop], $prop);
                }

                return $array[$prop];
            };

        return $mapper;
    }

    final protected static function CachePublicGettersAndSetters(string $class) : void
    {
        if (false === isset(self::$publicGetters[$class])) {
            self::$publicGetters[$class] = [];
            self::$publicSetters[$class] = [];

            if (is_a($class, DefinesOwnIdPropertiesInterface::class, true)) {
                self::$publicGetters[$class][] = 'id';
            }

            self::CachePublicGettersAndSettersProperties($class);
        }
    }

    final protected static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        foreach ($class::DaftObjectProperties() as $prop) {
            if (TypeUtilities::HasPublicMethod($class, $prop, false)) {
                self::$publicGetters[$class][] = $prop;
            }

            if (TypeUtilities::HasPublicMethod($class, $prop, true)) {
                self::$publicSetters[$class][] = $prop;
            }
        }
    }

    final protected static function CheckTypeDefinesOwnIdPropertiesIsImplementation(
        string $class
    ) : void {
        $properties = $class::DaftObjectIdProperties();

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

    private static function ThrowBecauseArrayJsonTypeNotValid(
        string $class,
        string $type,
        string $prop
    ) : void {
        if ('[]' === mb_substr($type, -2)) {
            throw new PropertyNotJsonDecodableShouldBeArrayException($class, $prop);
        }
        throw new PropertyNotJsonDecodableShouldBeArrayException($type, $prop);
    }
}
