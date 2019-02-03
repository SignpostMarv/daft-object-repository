<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftMagicPropertyAnalysis\PHPStan\ClassReflectionExtension as Base;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinitionAssistant;

/**
* @template T as DaftObject
*
* @template-extends Base<T>
*/
class ClassReflectionExtension extends Base
{
    const BOOL_DOES_NOT_HAVE_PROPERTY = false;

    protected function ObtainPropertyReflection(
        ClassReflection $ref,
        Broker $broker,
        string $propertyName
    ) : PropertyReflection {
        return new PropertyReflectionExtension($ref, $broker, $propertyName);
    }

    protected function MaybeRegisterTypesOrExitEarly(
        ClassReflection $classReflection,
        string $propertyName
    ) : ? bool {
        $className = $classReflection->getName();

        if ( ! is_a($className, DaftObject::class, true)) {
            return self::BOOL_DOES_NOT_HAVE_PROPERTY;
        }

        /**
        * @psalm-var class-string<DaftObject>
        */
        $className = $className;

        if (
            is_a($className, AbstractDaftObject::class, true) &&
            DefinitionAssistant::IsTypeUnregistered($className)
        ) {
            /**
            * @psalm-var class-string<AbstractDaftObject>
            */
            $className = $className;

            DefinitionAssistant::RegisterAbstractDaftObjectType($className);
        }

        return null;
    }
}
