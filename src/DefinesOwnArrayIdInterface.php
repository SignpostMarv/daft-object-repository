<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Interface for allowing daft object implementations to define their own ids.
*
* @property-read scalar[] $id
*/
interface DefinesOwnArrayIdInterface extends DefinesOwnIdPropertiesInterface
{
    /**
    * Get the integer value of the Id.
    *
    * @return scalar[]
    */
    public function GetId() : array;
}
