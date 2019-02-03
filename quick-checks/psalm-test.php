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

$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByType(ReadWriteJson::class);

$repo->ForgetDaftObject($a);

/**
* @psalm-var DaftObjectMemoryRepository<ReadWriteJson>
*/
$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByType(ReadWriteJson::class);

$repo->ForgetDaftObject($a);

$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject($b);

$repo->ForgetDaftObject($a);

/**
* @psalm-var DaftObjectMemoryRepository<ReadWriteJson>
*/
$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject($b);

$repo->ForgetDaftObject($a);

$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject($b);

$repo->RememberDaftObject($a);

/**
* @psalm-var DaftObjectMemoryRepository<ReadWriteJson>
*/
$repo = DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject($b);

$repo->RememberDaftObject($a);
