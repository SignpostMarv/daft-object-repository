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
use SignpostMarv\DaftObject\TypeParanoia;

/**
* @template-extends Base<DaftObject>
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

        if ( ! TypeParanoia::IsThingStrings($className, DaftObject::class)) {
            return self::BOOL_DOES_NOT_HAVE_PROPERTY;
        }

        /**
        * @psalm-var class-string<DaftObject>
        */
        $className = $className;

        if (
            DefinitionAssistant::IsTypeUnregistered($className) &&
            TypeParanoia::IsThingStrings($className, AbstractDaftObject::class)
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
