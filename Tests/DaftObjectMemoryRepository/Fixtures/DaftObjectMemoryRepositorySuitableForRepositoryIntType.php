<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DaftObjectMemoryRepository\Fixtures;

use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepository\Tests\SuitableForRepositoryType\Fixtures\SuitableForRepositoryIntType;

/**
* @template T as SuitableForRepositoryIntType
*
* @template-extends DaftObjectMemoryRepository<T>
*/
class DaftObjectMemoryRepositorySuitableForRepositoryIntType extends DaftObjectMemoryRepository
{
}
