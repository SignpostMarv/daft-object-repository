<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use InvalidArgumentException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use ReflectionMethod;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinesOwnUntypedIdInterface;

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
    * @var ClassReflection
    */
    protected $readableReflection;

    /**
    * @var ClassReflection
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

        $class = $classReflection->getName();

        $this->public = static::PropertyIsPublic($class, $property);

        $this->type = new MixedType();

        $get = 'Get' . ucfirst($property);
        $set = 'Set' . ucfirst($property);

        $this->readableReflection = $classReflection;
        $this->writeableReflection = $classReflection;

        if ($classReflection->getNativeReflection()->hasMethod($get)) {
            $refMethod = new ReflectionMethod($class, $get);

            $this->readableReflection = $this->SetGetterProps($refMethod);
        }

        if ($classReflection->getNativeReflection()->hasMethod($set)) {
            $refMethod = new ReflectionMethod($class, $set);

            $this->writeableReflection = $this->SetSetterProps($class, $refMethod);
        }
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
        if ($this->readable) {
            return $this->readableReflection;
        }

        return $this->writeableReflection;
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

    protected function SetSetterProps(string $class, ReflectionMethod $refMethod) : ClassReflection
    {
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

    protected static function DetermineDeclaringClass(
        Broker $broker,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        return $broker->getClassFromReflection(
            $refMethod->getDeclaringClass(),
            $refMethod->getDeclaringClass()->getName(),
            $refMethod->getDeclaringClass()->isAnonymous()
        );
    }

    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        return
            (is_a($className, DefinesOwnUntypedIdInterface::class, true) && 'id' === $property) ||
            in_array($property, $className::DaftObjectPublicGetters(), true) ||
            in_array($property, $className::DaftObjectPublicSetters(), true);
    }
}
