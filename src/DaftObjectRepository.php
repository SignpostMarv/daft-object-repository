<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftObjectRepository
{
    public function RememberDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    public function ForgetDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    /**
    * @param mixed $id
    */
    public function ForgetDaftObjectById($id) : void;

    public function RemoveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    /**
    * @param mixed $id
    */
    public function RemoveDaftObjectById($id) : void;

    /**
    * @param mixed $id
    */
    public function RecallDaftObject($id) : ? DaftObject;

    public static function DaftObjectRepositoryByType(
        string $type
    ) : DaftObjectRepository;

    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository;
}
