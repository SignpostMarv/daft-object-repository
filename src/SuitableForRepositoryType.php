<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @property mixed $id
*/
interface SuitableForRepositoryType extends
    DefinesOwnIdPropertiesInterface,
    DaftObjectCreatedByArray
{
}
