<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use ReflectionClass;
use ReflectionMethod;

/**
* Base daft object.
*/
abstract class AbstractDaftObject implements DaftObject
{
    /**
    * List of properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const PROPERTIES = [];

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const NULLABLE_PROPERTIES = [];

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const EXPORTABLE_PROPERTIES = [];

    /**
    * import/export definition for DaftJson.
    *
    * @var array<int, string>
    */
    const JSON_PROPERTIES = [];

    /**
    * @var array<string, array<int, string>>
    */
    private static $publicGetters = [];

    /**
    * @var array<string, array<int, string>>
    */
    private static $publicSetters = [];

    /**
    * Does some sanity checking.
    *
    * @see DefinesOwnIdPropertiesInterface
    * @see self::CheckTypeDefinesOwnIdProperties()
    */
    public function __construct()
    {
        self::CheckTypeDefinesOwnIdProperties(
            static::class,
            ($this instanceof DefinesOwnIdPropertiesInterface)
        );
    }

    public function __get(string $property)
    {
        return $this->DoGetSet($property, false);
    }

    public function __set(string $property, $v)
    {
        return $this->DoGetSet($property, true, $v);
    }

    /**
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void
    {
        $this->NudgePropertyValue($property, null);
    }

    /**
    * @return array<string, mixed>
    */
    public function __debugInfo() : array
    {
        $out = [];
        $publicGetters = static::DaftObjectPublicGetters();
        foreach (static::DaftObjectExportableProperties() as $prop) {
            $expectedMethod = 'Get' . ucfirst($prop);
            if ($this->__isset($prop) && in_array($prop, $publicGetters, true)) {
                $out[(string) $prop] = $this->$expectedMethod();
            }
        }

        return $out;
    }

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    final public static function DaftObjectProperties() : array
    {
        return static::PROPERTIES;
    }

    final public static function DaftObjectNullableProperties() : array
    {
        return static::NULLABLE_PROPERTIES;
    }

    final public static function DaftObjectExportableProperties() : array
    {
        return static::EXPORTABLE_PROPERTIES;
    }

    final public static function DaftObjectPublicGetters() : array
    {
        static::CachePublicGettersAndSetters();

        return self::$publicGetters[static::class];
    }

    final public static function DaftObjectPublicSetters() : array
    {
        static::CachePublicGettersAndSetters();

        return self::$publicSetters[static::class];
    }

    final public static function DaftObjectJsonProperties() : array
    {
        static::ThrowIfNotDaftJson();

        return static::JSON_PROPERTIES;
    }

    final public static function DaftObjectJsonPropertyNames() : array
    {
        $out = [];

        foreach (static::DaftObjectJsonProperties() as $k => $prop) {
            if (is_string($k)) {
                $prop = $k;
            }

            $out[] = $prop;
        }

        return $out;
    }

    protected static function ThrowIfNotDaftJson() : void
    {
        if (false === is_a(static::class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(static::class);
        }
    }

    final protected static function HasPublicMethod(
        ReflectionClass $classReflection,
        string $method
    ) : bool {
        if (
            $classReflection->hasMethod($method)
        ) {
            $methodReflection = new ReflectionMethod(static::class, $method);

            return
                $methodReflection->isPublic() &&
                false === $methodReflection->isStatic();
        }

        return false;
    }

    final protected static function CachePublicGettersAndSetters() : void
    {
        if (false === isset(self::$publicGetters[static::class])) {
            self::$publicGetters[static::class] = [];
            self::$publicSetters[static::class] = [];

            if (is_a(static::class, DefinesOwnIdPropertiesInterface::class, true)) {
                self::$publicGetters[static::class][] = 'id';
            }

            $classReflection = new ReflectionClass(static::class);

            foreach (static::DaftObjectProperties() as $property) {
                if (static::HasPublicMethod(
                        $classReflection,
                        static::DaftObjectMethodNameFromProperty($property)
                )) {
                    self::$publicGetters[static::class][] = $property;
                }

                if (static::HasPublicMethod(
                        $classReflection,
                        static::DaftObjectMethodNameFromProperty($property, true)
                )) {
                    self::$publicSetters[static::class][] = $property;
                }
            }
        }
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property property being nudged
    * @param mixed $value value to nudge property with
    *
    * @throws UndefinedPropertyException if $property is not in static::DaftObjectProperties()
    * @throws PropertyNotNullableException if $property is not in static::DaftObjectNullableProperties()
    * @throws PropertyNotRewriteableException if class is write-once read-many and $property was already changed
    */
    abstract protected function NudgePropertyValue(string $property, $value) : void;

    /**
    * Checks if a type correctly defines it's own id.
    *
    * @throws ClassDoesNotImplementClassException if $class is not an implementation of DefinesOwnIdPropertiesInterface
    * @throws ClassMethodReturnHasZeroArrayCountException if $class::DaftObjectIdProperties() does not contain at least one property
    * @throws ClassMethodReturnIsNotArrayOfStringsException if $class::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $class::DaftObjectIdProperties()
    */
    final protected static function CheckTypeDefinesOwnIdProperties(
        string $class,
        bool $throwIfNotImplementation = false
    ) : void {
        $interfaceCheck = $class;

        if (is_a($interfaceCheck, DefinesOwnIdPropertiesInterface::class, true)) {
            $properties = $class::DaftObjectIdProperties();

            if (count($properties) < 1) {
                throw new ClassMethodReturnHasZeroArrayCountException(
                    $class,
                    'DaftObjectIdProperties'
                );
            }

            foreach ($properties as $property) {
                if (false === is_string($property)) {
                    throw new ClassMethodReturnIsNotArrayOfStringsException(
                        $class,
                        'DaftObjectIdProperties'
                    );
                }
            }
        } elseif ($throwIfNotImplementation) {
            throw new ClassDoesNotImplementClassException(
                $class,
                DefinesOwnIdPropertiesInterface::class
            );
        }
    }

    protected static function DaftObjectMethodNameFromProperty(
        string $property,
        bool $SetNotGet = false
    ) : string {
        return ($SetNotGet ? 'Set' : 'Get') . ucfirst($property);
    }

    /**
    * @param mixed $v
    *
    * @return mixed
    */
    protected function DoGetSet(string $property, bool $SetNotGet, $v = null)
    {
        $expectedMethod = static::DaftObjectMethodNameFromProperty($property, $SetNotGet);
        $thingers = static::DaftObjectPublicGetters();

        if ($SetNotGet) {
            $thingers = static::DaftObjectPublicSetters();
        }

        if (false === in_array($property, $thingers, true)) {
            if (false === in_array($property, static::DaftObjectProperties(), true)) {
                throw new UndefinedPropertyException(static::class, $property);
            } elseif ($SetNotGet) {
                throw new NotPublicSetterPropertyException(static::class, $property);
            }

            throw new NotPublicGetterPropertyException(static::class, $property);
        }

        return $this->$expectedMethod($v);
    }
}
