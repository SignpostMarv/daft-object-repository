<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftJsonLinkedData
*
* @template-extends DaftJson<T>
*/
interface DaftJsonLinkedData extends DaftJson
{
}
