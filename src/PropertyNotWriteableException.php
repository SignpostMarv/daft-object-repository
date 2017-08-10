<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;
use TypeError;

class PropertyNotWriteableException extends TypeError
{
    public function __construct(
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Property not writeable: %s::$%s',
                $className,
                $property
            ),
            $code,
            $previous
        );
    }
}
