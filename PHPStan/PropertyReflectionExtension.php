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

class PropertyReflectionExtension extends Base
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

    /**
    * @psalm-suppress InvalidStringClass
    * @psalm-suppress MixedMethodCall
    */
    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        if ( ! TypeParanoia::IsSubThingStrings($className, DaftObject::class)) {
            return self::BOOL_CLASS_NOT_DAFTOBJECT;
        }

        return parent::PropertyIsPublic($className, $property);
    }
}
