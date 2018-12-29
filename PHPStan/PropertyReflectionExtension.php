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
use SignpostMarv\DaftObject\TypeUtilities;

class PropertyReflectionExtension implements PropertyReflection
{
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
        if (false === is_a($classReflection->getName(), DaftObject::class, true)) {
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
        return false === $this->isPublic();
    }

    public function isStatic() : bool
    {
        return false;
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

    protected function SetupReflections(ClassReflection $classReflection, string $property) : void
    {
        $class = $classReflection->getName();
        $get = static::MethodNameFromProperty($property);
        $set = static::MethodNameFromProperty($property, true);

        $this->writeableReflection = $this->readableReflection = $classReflection;

        if ($classReflection->getNativeReflection()->hasMethod($get)) {
            $this->readableReflection = $this->SetGetterProps(new ReflectionMethod($class, $get));
        }

        if ($classReflection->getNativeReflection()->hasMethod($set)) {
            $this->writeableReflection = $this->SetSetterProps($class, $set);
        }
    }

    protected function SetGetterProps(ReflectionMethod $refMethod) : ClassReflection
    {
        $this->readable = true;

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

    protected function SetSetterProps(string $class, string $set) : ClassReflection
    {
        $refMethod = new ReflectionMethod($class, $set);
        $this->writeable = true;

        $refParam = $refMethod->getParameters()[0];

        if ($refParam->hasType()) {
            $this->type = TypehintHelper::decideTypeFromReflection(
                $refParam->getType(),
                null,
                $class,
                false
            );
        }

        return static::DetermineDeclaringClass($this->broker, $refMethod);
    }

    protected static function MethodNameFromProperty(
        string $prop,
        bool $SetNotGet = false
    ) : string {
        return TypeUtilities::MethodNameFromProperty($prop, $SetNotGet);
    }

    protected static function DetermineDeclaringClass(
        Broker $broker,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        $reflectionClass = $refMethod->getDeclaringClass();

        $filename = null;
        if (false !== $reflectionClass->getFileName()) {
            $filename = $reflectionClass->getFileName();
        }

        return $broker->getClassFromReflection(
            $reflectionClass,
            $reflectionClass->getName(),
            $reflectionClass->isAnonymous() ? $filename : null
        );
    }

    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        return
            (is_a($className, DefinesOwnIdPropertiesInterface::class, true) && 'id' === $property) ||
            in_array($property, (array) $className::DaftObjectPublicGetters(), true) ||
            in_array($property, (array) $className::DaftObjectPublicSetters(), true);
    }
}
