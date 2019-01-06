<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;

class ClassDoesNotImplementClassException extends IncorrectlyImplementedTypeException
{
    public function __construct(
        string $class,
        string $doesNotImplementClass,
        int $code = self::INT_DEFAULT_CODE,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('%s does not implement %s', $class, $doesNotImplementClass),
            $code,
            $previous
        );
    }
}
