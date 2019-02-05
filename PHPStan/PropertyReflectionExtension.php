<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use SignpostMarv\DaftMagicPropertyAnalysis\PHPStan\PropertyReflectionExtension as Base;
use SignpostMarv\DaftObject\DaftObject;

/**
* @template T as \SignpostMarv\DaftObject\DaftObject
*
* @template-extends Base<T>
*/
class PropertyReflectionExtension extends Base
{
}
