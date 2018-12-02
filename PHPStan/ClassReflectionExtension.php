<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\TypeUtilities;

class ClassReflectionExtension implements BrokerAwareExtension, PropertiesClassReflectionExtension
{
    /**
    * @var Broker|null
    */
    private $broker;

    public function setBroker(Broker $broker) : void
    {
        $this->broker = $broker;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        $className = $classReflection->getName();

        $property = ucfirst($propertyName);
        $getter = static::MethodNameFromProperty($property);
        $setter = static::MethodNameFromProperty($property, true);

        return
            is_a($className, DaftObject::class, true) &&
            (
                $classReflection->getNativeReflection()->hasMethod($getter) ||
                $classReflection->getNativeReflection()->hasMethod($setter)
            );
    }

    public function getProperty(ClassReflection $ref, string $propertyName) : PropertyReflection
    {
        /**
        * @var Broker
        */
        $broker = $this->broker;

        return new PropertyReflectionExtension($ref, $broker, $propertyName);
    }

    protected static function MethodNameFromProperty(
        string $prop,
        bool $SetNotGet = false
    ) : string {
        return TypeUtilities::MethodNameFromProperty($prop, $SetNotGet);
    }
}
