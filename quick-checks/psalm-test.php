<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteJson;

$a = new ReadWrite();
$b = new ReadWriteJson();

/**
* @psalm-var DaftObjectMemoryRepository<ReadWriteJson>
*/
$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByType(ReadWriteJson::class);

$repo->ForgetDaftObject($a);
