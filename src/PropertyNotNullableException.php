<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;
use TypeError;

class PropertyNotNullableException extends TypeError
{
    public function __construct(
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Property not nullable: %s::$%s',
                $className,
                $property
            ),
            $code,
            $previous
        );
    }
}
