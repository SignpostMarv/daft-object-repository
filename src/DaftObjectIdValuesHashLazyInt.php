<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DaftObjectIdValuesHashLazyInt
{
    /**
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdHash()
    */
    public static function DaftObjectIdHash(DefinesOwnIdPropertiesInterface $object) : string
    {
        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $id[] = $object->$prop;
        }

        return static::DaftObjectIdValuesHash($id);
    }

    /**
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdValuesHash()
    */
    public static function DaftObjectIdValuesHash(array $id) : string
    {
        static $ids = [];

        $className = static::class;

        $objectIds = '';
        foreach (array_values($id) as $i => $idVal) {
            if ($i >= 1) {
                $objectIds .= '::';
            }
            $objectIds .= (string) $idVal;
        }

        if (false === isset($ids[$className], $ids[$className][$objectIds])) {
            $ids[$className] = $ids[$className] ?? [];
            $ids[$className][$objectIds] = (string) count($ids[$className]);
        }

        return $ids[$className][$objectIds];
    }
}
