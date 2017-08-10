<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use RuntimeException;
use Throwable;

class UndefinedPropertyException extends RuntimeException
{
    public function __construct(
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Undefined property: %s::$%s',
                $className,
                $property
            ),
            $code,
            $previous
        );
    }
}
