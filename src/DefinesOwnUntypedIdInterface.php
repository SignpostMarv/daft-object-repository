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
*/
interface DefinesOwnUntypedIdInterface extends DefinesOwnIdPropertiesInterface
{
    /**
    * Get the value of the Id.
    *
    * @return mixed
    */
    public function GetId();
}
