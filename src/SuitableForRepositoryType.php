<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface SuitableForRepositoryType extends
    DefinesOwnIdPropertiesInterface,
    DaftObjectCreatedByArray
{
}
