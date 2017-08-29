<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;

/**
* Exception thrown when a property is not writeable.
*/
class PropertyNotWriteableException extends AbstractPropertyNotThingableError
{
    /**
    * Wraps to TypeError::__construct().
    *
    * @param string $className name of the class on which the property is not writeable
    * @param string $property name of the property which is not writeable
    * @param int $code @see TypeError::__construct()
    * @param Throwable|null $previous @see TypeError::__construct()
    */
    public function __construct(
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            'writeable',
            $className,
            $property,
            $code,
            $previous
        );
    }
}
