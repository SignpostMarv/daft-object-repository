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
* Exception thrown when a property is not nullable.
*/
class PropertyNotNullableException extends AbstractPropertyNotThingableException
{
    /**
    * Wraps to AbstractPropertyNotThingableException::__construct().
    *
    * @param string $className name of the class on which the property is not nullable
    * @param string $property name of the property which is not nullable
    * @param int $code @see Exception::__construct()
    * @param Throwable|null $previous @see Exception::__construct()
    */
    public function __construct(
        string $className,
        string $property,
        int $code = self::INT_DEFAULT_CODE,
        Throwable $previous = null
    ) {
        parent::__construct('nullable', $className, $property, $code, $previous);
    }
}
