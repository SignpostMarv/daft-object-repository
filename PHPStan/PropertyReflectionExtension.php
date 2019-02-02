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
use SignpostMarv\DaftMagicPropertyAnalysis\PHPStan\PropertyReflectionExtension as Base;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\TypeParanoia;

/**
* @template-extends Base<DaftObject>
*/
class PropertyReflectionExtension extends Base
{
    const BOOL_CLASS_NOT_DAFTOBJECT = false;

    public function __construct(ClassReflection $classReflection, Broker $broker, string $property)
    {
        if ( ! TypeParanoia::IsThingStrings($classReflection->getName(), DaftObject::class)) {
            throw new InvalidArgumentException(sprintf('%s is not an implementation of %s',
                $classReflection->getName(),
                DaftObject::class
            ));
        }

        parent::__construct($classReflection, $broker, $property);
    }
}
