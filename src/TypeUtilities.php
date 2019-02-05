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
    const BOOL_EXPECTING_NON_PUBLIC_METHOD = false;

    const BOOL_EXPECTING_GETTER = false;

    const BOOL_DEFAULT_THROWIFNOTIMPLEMENTATION = false;

    const BOOL_DEFAULT_EXPECTING_NON_PUBLIC_METHOD = true;

    const BOOL_METHOD_IS_PUBLIC = true;

    const BOOL_METHOD_IS_NON_PUBLIC = false;

    const BOOL_DEFAULT_SET_NOT_GET = false;

    const SUPPORTED_INVALID_LEADING_CHARACTERS = [
        '@',
    ];

    /**
    * @var array<string, array<string, bool>>
    *
    * @psalm-var array<class-string<DaftObject>, array<string, bool>>
    */
    private static $Getters = [];

    /**
    * @var array<string, array<int, string>>
    *
    * @psalm-var array<class-string<DaftObject>, array<int, string>>
    */
    private static $publicSetters = [];

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(array_filter(self::$Getters[$class]));
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicOrProtectedGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(self::$Getters[$class]);
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    public static function DaftObjectPublicSetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicSetters[$class];
    }

    public static function MethodNameFromProperty(
        string $prop,
        bool $SetNotGet = self::BOOL_DEFAULT_SET_NOT_GET
    ) : string {
        if (
            in_array(
                mb_substr($prop, 0, 1),
                self::SUPPORTED_INVALID_LEADING_CHARACTERS,
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            )
        ) {
            return ($SetNotGet ? 'Alter' : 'Obtain') . ucfirst(mb_substr($prop, 1));
        }

        return ($SetNotGet ? 'Set' : 'Get') . ucfirst($prop);
    }

    private static function HasMethod(
        string $class,
        string $property,
        bool $SetNotGet,
        bool $pub = self::BOOL_DEFAULT_EXPECTING_NON_PUBLIC_METHOD
    ) : bool {
        $method = static::MethodNameFromProperty($property, $SetNotGet);

        try {
            $ref = new ReflectionMethod($class, $method);

            return ($pub ? $ref->isPublic() : $ref->isProtected()) && false === $ref->isStatic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    private static function CachePublicGettersAndSetters(string $class) : void
    {
        if (false === isset(self::$Getters[$class])) {
            self::$Getters[$class] = [];
            self::$publicSetters[$class] = [];

            if (
                is_a(
                    $class,
                    DefinesOwnIdPropertiesInterface::class,
                    true
                )
            ) {
                self::$Getters[$class]['id'] = self::BOOL_METHOD_IS_PUBLIC;
            }

            /**
            * @psalm-var class-string<DaftObject>
            */
            $class = $class;

            static::CachePublicGettersAndSettersProperties($class);
        }
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    private static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        foreach (
            DefinitionAssistant::ObtainExpectedProperties($class) as $prop
        ) {
            if (static::HasMethod($class, $prop, self::BOOL_EXPECTING_GETTER)) {
                self::$Getters[$class][$prop] = self::BOOL_METHOD_IS_PUBLIC;
            } elseif (static::HasMethod(
                $class,
                $prop,
                self::BOOL_EXPECTING_GETTER,
                self::BOOL_EXPECTING_NON_PUBLIC_METHOD
            )) {
                self::$Getters[$class][$prop] = self::BOOL_METHOD_IS_NON_PUBLIC;
            }

            if (static::HasMethod($class, $prop, true)) {
                self::$publicSetters[$class][] = $prop;
            }
        }
    }
}
