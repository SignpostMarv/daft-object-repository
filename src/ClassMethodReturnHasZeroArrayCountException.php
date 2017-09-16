<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;

class ClassMethodReturnHasZeroArrayCountException extends IncorrectlyImplementedTypeError
{
    public function __construct(
        string $class,
        string $method,
        string $labelOfReturnType = 'property',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                '%s::%s() must return at least one %s',
                $class,
                $method,
                $labelOfReturnType
            ),
            $code,
            $previous
        );
    }
}
