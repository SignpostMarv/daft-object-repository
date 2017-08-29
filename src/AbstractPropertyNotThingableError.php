<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Throwable;
use TypeError;

/**
* Error thrown when a property is not thingable.
*/
abstract class AbstractPropertyNotThingableError extends TypeError
{
    /**
    * Wraps to TypeError::__construct().
    *
    * @param string $thing the type of thing that the $property cannot do, i.e. writeable, nullable
    * @param string $className name of the class on which the property is not thingable
    * @param string $property name of the property which is not thingable
    * @param int $code @see TypeError::__construct()
    * @param Throwable|null $previous @see TypeError::__construct()
    */
    public function __construct(
        string $thing,
        string $className,
        string $property,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Property not %s: %s::$%s',
                $thing,
                $className,
                $property
            ),
            $code,
            $previous
        );
    }
}
