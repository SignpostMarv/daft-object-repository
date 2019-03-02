<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DefinesOwnIdPropertiesInterface
*/
trait DaftObjectIdValuesHashLazyInt
{
    /**
    * @var array<string, array<string, string>>
    *
    * @psalm-var array<class-string<T>, array<string, string>>
    */
    private static $ids = [];

    /**
    * @psalm-param T $object
    *
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdHash()
    */
    public static function DaftObjectIdHash(DefinesOwnIdPropertiesInterface $object) : string
    {
        return static::DaftObjectIdValuesHash(array_map(
            function (string $prop) use ($object) : string {
                /**
                * @var scalar|array|object|null
                */
                $val = $object->__get($prop);

                return static::VarExportNonScalars($val);
            },
            $object::DaftObjectIdProperties()
        ));
    }

    /**
    * @param (scalar|array|object|null)[] $id
    *
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdValuesHash()
    */
    public static function DaftObjectIdValuesHash(array $id) : string
    {
        $className = static::class;

        $objectIds = implode(
            '::',
            array_map(
                /**
                * @param scalar|array|object|null $val
                */
                function ($val) : string {
                    return static::VarExportNonScalars($val);
                },
                $id
            )
        );

        if ( ! isset(self::$ids[$className])) {
            self::$ids[$className] = [];
        }

        if ( ! isset(self::$ids[$className][$objectIds])) {
            self::$ids[$className][$objectIds] = static::VarExportNonScalars(count(
                self::$ids[$className]
            ));
        }

        return self::$ids[$className][$objectIds];
    }

    /**
    * @param scalar|array|object|null $maybe
    */
    private static function VarExportNonScalars($maybe) : string
    {
        if (is_string($maybe)) {
            return $maybe;
        }

        return
            is_scalar($maybe)
                ? (string) $maybe
                : var_export($maybe, true);
    }
}
