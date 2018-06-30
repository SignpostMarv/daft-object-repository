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
    * @var array<string, array<string, string>>
    */
    private static $ids = [];

    /**
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdHash()
    */
    public static function DaftObjectIdHash(DefinesOwnIdPropertiesInterface $object) : string
    {
        $id = [];

        /**
        * @var array<int, string> $properties
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            /**
            * @var scalar|null|array|object $val
            */
            $val = $object->$prop;

            $id[] = $val;
        }

        return static::DaftObjectIdValuesHash($id);
    }

    /**
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdValuesHash()
    */
    public static function DaftObjectIdValuesHash(array $id) : string
    {
        $className = static::class;

        $objectIds = '';

        /**
        * @var array<int, string> $id
        */
        $id = array_values($id);

        foreach ($id as $i => $idVal) {
            if ($i >= 1) {
                $objectIds .= '::';
            }
            $objectIds .= (string) $idVal;
        }

        if ( ! isset(self::$ids[$className])) {
            self::$ids[$className] = [];
        }

        if ( ! isset(self::$ids[$className][$objectIds])) {
            self::$ids[$className][$objectIds] = (string) count(self::$ids[$className]);
        }

        return (string) self::$ids[$className][$objectIds];
    }
}
