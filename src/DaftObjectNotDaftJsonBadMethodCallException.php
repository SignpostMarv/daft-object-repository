<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use BadMethodCallException;
use Throwable;

class DaftObjectNotDaftJsonBadMethodCallException extends BadMethodCallException
{
    const INT_DEFAULT_CODE = 0;

    public function __construct(string $class, int $code = self::INT_DEFAULT_CODE, Throwable $previous = null)
    {
        parent::__construct(($class . ' does not implement ' . DaftJson::class), $code, $previous);
    }
}
