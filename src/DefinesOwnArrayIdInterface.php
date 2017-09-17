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
interface DefinesOwnArrayIdInterface extends DefinesOwnUntypedIdInterface
{
    /**
    * Get the integer value of the Id.
    */
    public function GetId() : array;
}