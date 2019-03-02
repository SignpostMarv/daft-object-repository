<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DefinesOwnIdPropertiesInterface\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;

/**
* @template T as float
*
* @template-extends DefinesOwnScalarIdProperties<T>
*/
class DefinesOwnFloatIdProperties extends DefinesOwnScalarIdProperties
{
}
