<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Exceptions;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\Exceptions\Factory as Base;
use Throwable;

abstract class Factory extends Base
{
    /**
    * @psalm-param class-string<DaftObject> $type
    * @psalm-param class-string<DaftObjectRepository> $implementation
    */
    public static function DaftObjectNotRecalledExceptionWithTypeFromArgumentOnImplementation(
        int $argument,
        string $method,
        string $type,
        string $implementation,
        string $implementation_method = 'RecallDaftObject',
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : DaftObjectNotRecalledException {
        /**
        * @var DaftObjectNotRecalledException
        */
        $out = static::Exception(
            DaftObjectNotRecalledException::class,
            $code,
            $previous,
            DaftObjectNotRecalledException::class,
            'Argument %u passed to %s() did not resolve to an instance of %s from %s::%s()',
            $argument,
            $method,
            $type,
            $implementation,
            $implementation_method
        );

        return $out;
    }
}
