<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use BadMethodCallException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinitionAssistant;
use SignpostMarv\DaftObject\TypeParanoia;
use SignpostMarv\DaftObject\TypeUtilities;

class ClassReflectionExtension implements BrokerAwareExtension, PropertiesClassReflectionExtension
{
    const BOOL_DOES_NOT_HAVE_PROPERTY = false;

    const BOOL_SETNOTGET_SETTER = true;

    const BOOL_SETNOTGET_GETTER = false;

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

        if ( ! TypeParanoia::IsThingStrings($className, DaftObject::class)) {
            return self::BOOL_DOES_NOT_HAVE_PROPERTY;
        } elseif (
            DefinitionAssistant::IsTypeUnregistered($className) &&
            TypeParanoia::IsThingStrings($className, AbstractDaftObject::class)
        ) {
            DefinitionAssistant::RegisterAbstractDaftObjectType($className);
        }

        $property = ucfirst($propertyName);
        $getter = TypeUtilities::MethodNameFromProperty($property, self::BOOL_SETNOTGET_GETTER);
        $setter = TypeUtilities::MethodNameFromProperty($property, self::BOOL_SETNOTGET_SETTER);

        return
            TypeParanoia::MaybeInArray(
                $property,
                DefinitionAssistant::ObtainExpectedProperties($className)
            ) ||
            $classReflection->getNativeReflection()->hasMethod($getter) ||
            $classReflection->getNativeReflection()->hasMethod($setter);
    }

    public function getProperty(ClassReflection $ref, string $propertyName) : PropertyReflection
    {
        if ( ! ($this->broker instanceof Broker)) {
            throw new BadMethodCallException(
                'Broker expected to be specified when calling ' .
                __METHOD__
            );
        }

        return new PropertyReflectionExtension($ref, $this->broker, $propertyName);
    }
}
