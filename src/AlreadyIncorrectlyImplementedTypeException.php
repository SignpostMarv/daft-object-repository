<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;

class AlreadyIncorrectlyImplementedTypeException extends IncorrectlyImplementedTypeException
{
    public function __construct(
        string $class,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                '%s already determined to be incorrectly implemented',
                $class
            ),
            $code,
            $previous
        );
    }
}
