<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Exception;

class IncorrectlyImplementedTypeException extends Exception
{
    const INT_DEFAULT_CODE = 0;
}
