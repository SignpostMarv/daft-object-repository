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
* @property-read string $id
*/
interface DefinesOwnStringIdInterface extends DefinesOwnIdPropertiesInterface
{
    /**
    * Get the integer value of the Id.
    */
    public function GetId() : string;
}
