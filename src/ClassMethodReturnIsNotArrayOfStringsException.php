<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;

class ClassMethodReturnIsNotArrayOfStringsException extends IncorrectlyImplementedTypeException
{
    public function __construct(
        string $class,
        string $method,
        int $code = self::INT_DEFAULT_CODE,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('%s::%s() does not return string[]', $class, $method),
            $code,
            $previous
        );
    }
}
