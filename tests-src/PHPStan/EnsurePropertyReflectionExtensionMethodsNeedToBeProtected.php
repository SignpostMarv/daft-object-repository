<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use Nette\DI\Container;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Broker\BrokerFactory;
use ReflectionMethod;

class EnsurePropertyReflectionExtensionMethodsNeedToBeProtected extends PropertyReflectionExtension
{
    public static function EnsurePropertyIsPublic(string $className, string $property) : bool
    {
        return static::PropertyIsPublic($className, $property);
    }
}
