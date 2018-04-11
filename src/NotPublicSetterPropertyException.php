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
* Exception thrown when a property is not defined.
*/
class NotPublicSetterPropertyException extends AbstractPropertyNotThingableException
{
    /**
    * Wraps to AbstractPropertyNotThingableException::__construct().
    *
    * @param string $className name of the class on which the property is not a public setter
    * @param string $property name of the property which is not a public setter
    * @param int $code @see Exception::__construct()
    * @param Throwable|null $previous @see Exception::__construct()
    */
    public function __construct(
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct('a public setter', $className, $property, $code, $previous);
    }
}
