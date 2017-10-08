<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftObject\DaftObject;

class ClassReflectionExtension implements
    BrokerAwareClassReflectionExtension,
    PropertiesClassReflectionExtension
{
    /**
    * @var Broker|null
    */
    private $broker;

    public function setBroker(Broker $broker) : void
    {
        $this->broker = $broker;
    }

    public function hasProperty(
        ClassReflection $classReflection,
        string $propertyName
    ) : bool {
        $className = $classReflection->getName();

        $property = ucfirst($propertyName);

        return
            is_a($className, DaftObject::class, true) &&
            (
                $classReflection->getNativeReflection()->hasMethod(
                    'Get' .
                    $property
                ) ||
                $classReflection->getNativeReflection()->hasMethod(
                    'Set' .
                    $property
                )
            );
    }

    public function getProperty(
        ClassReflection $classReflection,
        string $propertyName
    ) : PropertyReflection {
        /**
        * @var Broker $broker
        */
        $broker = $this->broker;

        return new PropertyReflectionExtension(
            $classReflection,
            $broker,
            $propertyName
        );
    }
}
