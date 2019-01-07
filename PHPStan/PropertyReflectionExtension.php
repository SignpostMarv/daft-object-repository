<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use BadMethodCallException;
use InvalidArgumentException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use ReflectionMethod;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\TypeParanoia;
use SignpostMarv\DaftObject\TypeUtilities;

class PropertyReflectionExtension implements PropertyReflection
{
    const REF_PARAM_INDEX = 0;

    const BOOL_IS_WRITEABLE = true;

    const BOOL_IS_READABLE = true;

    const BOOL_SETNOTGET_SETTER = true;

    const BOOL_SETNOTGET_GETTER = false;

    const BOOL_CLASS_NOT_DAFTOBJECT = false;

    const BOOL_REFLECTION_NO_FILE = false;

    const BOOL_NOT_VARIADIC = false;

    const BOOL_IS_STATIC = false;

    /**
    * @var Type
    */
    protected $type;

    /**
    * @var Broker
    */
    protected $broker;

    /**
    * @var bool
    */
    protected $readable = false;

    /**
    * @var bool
    */
    protected $writeable = false;

    /**
    * @var bool
    */
    protected $public;

    /**
    * @var ClassReflection|null
    */
    protected $readableReflection;

    /**
    * @var ClassReflection|null
    */
    protected $writeableReflection;

    public function __construct(ClassReflection $classReflection, Broker $broker, string $property)
    {
        if ( ! TypeParanoia::IsThingStrings($classReflection->getName(), DaftObject::class)) {
            throw new InvalidArgumentException(sprintf('%s is not an implementation of %s',
                $classReflection->getName(),
                DaftObject::class
            ));
        }

        $this->broker = $broker;

        $this->public = static::PropertyIsPublic($classReflection->getName(), $property);

        $this->type = new MixedType();

        $this->SetupReflections($classReflection, $property);
    }

    public function getType() : Type
    {
        return $this->type;
    }

    public function isReadable() : bool
    {
        return $this->readable;
    }

    public function isWritable() : bool
    {
        return $this->writeable;
    }

    public function isPublic() : bool
    {
        return $this->public;
    }

    public function isPrivate() : bool
    {
        return ! $this->isPublic();
    }

    public function isStatic() : bool
    {
        return self::BOOL_IS_STATIC;
    }

    public function getDeclaringClass() : ClassReflection
    {
        $reflection = $this->readable ? $this->readableReflection : $this->writeableReflection;

        if ( ! ($reflection instanceof ClassReflection)) {
            throw new BadMethodCallException(
                static::class .
                '::SetupReflections() was not called before ' .
                __METHOD__ .
                ' was called!'
            );
        }

        return $reflection;
    }

    protected static function DetermineDeclaringClass(
        Broker $broker,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        $reflectionClass = $refMethod->getDeclaringClass();

        $filename = null;
        if (self::BOOL_REFLECTION_NO_FILE !== $reflectionClass->getFileName()) {
            $filename = $reflectionClass->getFileName();
        }

        return $broker->getClassFromReflection(
            $reflectionClass,
            $reflectionClass->getName(),
            $reflectionClass->isAnonymous() ? $filename : null
        );
    }

    /**
    * @psalm-suppress InvalidStringClass
    * @psalm-suppress MixedMethodCall
    */
    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        if ( ! TypeParanoia::IsSubThingStrings($className, DaftObject::class)) {
            return self::BOOL_CLASS_NOT_DAFTOBJECT;
        }

        return
            (
                TypeParanoia::IsThingStrings(
                    $className,
                    DefinesOwnIdPropertiesInterface::class
                ) &&
                'id' === $property
            ) ||
            TypeParanoia::MaybeInMaybeArray($property, $className::DaftObjectPublicGetters()) ||
            TypeParanoia::MaybeInMaybeArray($property, $className::DaftObjectPublicSetters());
    }

    private function SetupReflections(ClassReflection $classReflection, string $property) : void
    {
        $class = $classReflection->getName();
        $get = TypeUtilities::MethodNameFromProperty($property, self::BOOL_SETNOTGET_GETTER);
        $set = TypeUtilities::MethodNameFromProperty($property, self::BOOL_SETNOTGET_SETTER);

        $this->writeableReflection = $this->readableReflection = $classReflection;

        if ($classReflection->getNativeReflection()->hasMethod($get)) {
            $this->readableReflection = $this->SetGetterProps(new ReflectionMethod($class, $get));
        }

        if ($classReflection->getNativeReflection()->hasMethod($set)) {
            $this->writeableReflection = $this->SetSetterProps($class, $set);
        }
    }

    private function SetGetterProps(ReflectionMethod $refMethod) : ClassReflection
    {
        $this->readable = self::BOOL_IS_READABLE;

        if ($refMethod->isStatic()) {
            throw new InvalidArgumentException(
                'Implementations of ' .
                DaftObject::class .
                ' must not contain static getters.'
            );
        }

        if ($refMethod->hasReturnType()) {
            $this->type = TypehintHelper::decideTypeFromReflection($refMethod->getReturnType());
        }

        return static::DetermineDeclaringClass($this->broker, $refMethod);
    }

    private function SetSetterProps(string $class, string $set) : ClassReflection
    {
        $refMethod = new ReflectionMethod($class, $set);
        $this->writeable = self::BOOL_IS_WRITEABLE;

        $refParam = $refMethod->getParameters()[self::REF_PARAM_INDEX];

        if ($refParam->hasType()) {
            $this->type = TypehintHelper::decideTypeFromReflection(
                $refParam->getType(),
                null,
                $class,
                self::BOOL_NOT_VARIADIC
            );
        }

        return static::DetermineDeclaringClass($this->broker, $refMethod);
    }
}
