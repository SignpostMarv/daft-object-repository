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
* @template-extends Base<DaftObject>
*/
class PropertyReflectionExtension extends Base
{
}
