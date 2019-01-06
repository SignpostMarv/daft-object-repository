<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Exception;
use Throwable;

/**
* Error thrown when a property is not thingable.
*/
abstract class AbstractPropertyNotThingableException extends Exception
{
    const INT_DEFAULT_CODE = 0;

    /**
    * Wraps to Exception::__construct().
    *
    * @param string $thing the type of thing that the $property cannot do, i.e. writeable, nullable
    * @param string $className name of the class on which the property is not thingable
    * @param string $property name of the property which is not thingable
    * @param int $code @see Exception::__construct()
    * @param Throwable|null $previous @see Exception::__construct()
    */
    public function __construct(
        string $thing,
        string $className,
        string $property,
        int $code = self::INT_DEFAULT_CODE,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('Property not %s: %s::$%s', $thing, $className, $property),
            $code,
            $previous
        );
    }
}
