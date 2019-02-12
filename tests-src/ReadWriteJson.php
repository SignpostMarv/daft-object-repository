<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as ReadWriteJson
*
* @template-implements DaftJson<T>
*/
class ReadWriteJson extends ReadWrite implements DaftJson
{
}
