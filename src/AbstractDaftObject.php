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
    * @var string[]
    */
    const PROPERTIES = [];

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @var string[]
    */
    const NULLABLE_PROPERTIES = [];

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @var string[]
    */
    const EXPORTABLE_PROPERTIES = [];

    /**
    * import/export definition for DaftJson.
    */
    const JSON_PROPERTIES = [];

    /**
    * @var string[][]
    */
    private static $publicGetters = [];

    /**
    * @var string[][]
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
        if (
            ($this instanceof DefinesOwnIdPropertiesInterface)
        ) {
            self::CheckTypeDefinesOwnIdProperties($this);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function __get(string $property)
    {
        return $this->DoGetSet(
            $property,
            false,
            NotPublicGetterPropertyException::class
        );
    }

    /**
    * {@inheritdoc}
    */
    public function __set(string $property, $v)
    {
        return $this->DoGetSet(
            $property,
            true,
            NotPublicSetterPropertyException::class,
            $v
        );
    }

    /**
    * {@inheritdoc}
    *
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void
    {
        $this->NudgePropertyValue($property, null);
    }

    /**
    * {@inheritdoc}
    */
    public function __debugInfo() : array
    {
        $out = [];
        $publicGetters = static::DaftObjectPublicGetters();
        foreach (static::DaftObjectExportableProperties() as $prop) {
            $expectedMethod = 'Get' . ucfirst($prop);
            if (
                $this->__isset($prop) &&
                in_array($prop, $publicGetters, true)
            ) {
                $out[$prop] = $this->$expectedMethod();
            }
        }

        return $out;
    }

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return string[]
    */
    final public static function DaftObjectProperties() : array
    {
        return static::PROPERTIES;
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectNullableProperties() : array
    {
        return static::NULLABLE_PROPERTIES;
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectExportableProperties() : array
    {
        return static::EXPORTABLE_PROPERTIES;
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectPublicGetters() : array
    {
        static::CachePublicGettersAndSetters();

        return self::$publicGetters[static::class];
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectPublicSetters() : array
    {
        static::CachePublicGettersAndSetters();

        return self::$publicSetters[static::class];
    }

    /**
    * {@inheritdoc}
    */
    final public static function DaftObjectJsonProperties() : array
    {
        if (false === is_a(static::class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException(
                static::class
            );
        }

        return static::JSON_PROPERTIES;
    }

    protected static function CachePublicGettersAndSetters() : void
    {
        $refreshGetters = false === isset(self::$publicGetters[static::class]);
        $refreshSetters = false === isset(self::$publicSetters[static::class]);

        if ($refreshGetters) {
            self::$publicGetters[static::class] = [];

            if (
                is_a(
                    static::class,
                    DefinesOwnIdPropertiesInterface::class,
                    true
                )
            ) {
                self::$publicGetters[static::class][] = 'id';
            }
        }

        if ($refreshSetters) {
            self::$publicSetters[static::class] = [];
        }

        if ($refreshGetters || $refreshSetters) {
            $classReflection = new ReflectionClass(static::class);

            foreach (static::DaftObjectProperties() as $property) {
                $getter = 'Get' . ucfirst($property);

                $setter = 'Set' . ucfirst($property);

                if (
                    $refreshGetters &&
                    $classReflection->hasMethod($getter) &&
                    (
                        $methodReflection = new ReflectionMethod(
                            static::class,
                            $getter
                        )
                    )->isPublic() &&
                    false === $methodReflection->isStatic()
                ) {
                    self::$publicGetters[static::class][] = $property;
                }

                if (
                    $refreshSetters &&
                    $classReflection->hasMethod($setter) &&
                    (
                        $methodReflection = new ReflectionMethod(
                            static::class,
                            $setter
                        )
                    )->isPublic() &&
                    false === $methodReflection->isStatic()
                ) {
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
    abstract protected function NudgePropertyValue(
        string $property,
        $value
    ) : void;

    /**
    * Checks if a type correctly defines it's own id.
    *
    * @param DaftObject $object
    *
    * @throws ClassDoesNotImplementClassException if $object is not an implementation of DefinesOwnIdPropertiesInterface
    * @throws ClassMethodReturnHasZeroArrayCountException if $object::DaftObjectIdProperties() does not contain at least one property
    * @throws ClassMethodReturnIsNotArrayOfStringsException if $object::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $object::DaftObjectIdProperties()
    */
    final protected static function CheckTypeDefinesOwnIdProperties(
        DaftObject $object
    ) : void {
        $class = get_class($object);
        if (false === ($object instanceof DefinesOwnIdPropertiesInterface)) {
            throw new ClassDoesNotImplementClassException(
                $class,
                DefinesOwnIdPropertiesInterface::class
            );
        }

        /**
        * @var DefinesOwnIdPropertiesInterface $object
        */
        $object = $object;

        $properties = $object::DaftObjectIdProperties();

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
    }

    /**
    * @param mixed $v
    *
    * @return mixed
    */
    protected function DoGetSet(
        string $property,
        bool $SetNotGet,
        string $notPublic,
        $v = null
    ) {
        if (
            (
                'id' !== $property ||
                false === ($this instanceof DefinesOwnIdPropertiesInterface)
            ) &&
            false === in_array($property, static::DaftObjectProperties(), true)
        ) {
            throw new UndefinedPropertyException(static::class, $property);
        } elseif (
            false === (
                in_array(
                    $property,
                    (
                        $SetNotGet
                            ? static::DaftObjectPublicSetters()
                            : static::DaftObjectPublicGetters()
                    ),
                    true
                )
            )
        ) {
            throw new $notPublic(
                static::class,
                $property
            );
        }

        $expectedMethod = ($SetNotGet ? 'Set' : 'Get') . ucfirst($property);

        return $this->$expectedMethod($v);
    }
}
