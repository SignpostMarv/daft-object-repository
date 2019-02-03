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
}
