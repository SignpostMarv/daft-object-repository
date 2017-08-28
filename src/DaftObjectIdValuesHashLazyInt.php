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
    public static function DaftObjectIdHash(
        DefinesOwnIdPropertiesInterface $object
    ) : string {
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

        if (isset($ids[$className]) === false) {
            $ids[$className] = [];
        }

        $objectIds = '';
        foreach (array_values($id) as $i => $idVal) {
            if ($i >= 1) {
                $objectIds .= '::';
            }
            $objectIds .= (string) $idVal;
        }

        if (isset($ids[$className][$objectIds]) === false) {
            $ids[$className][$objectIds] = (string) count($ids[$className]);
        }

        return $ids[$className][$objectIds];
    }
}
