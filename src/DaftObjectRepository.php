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
    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * Allow data to be persisted without assuming the object exists, i.e. if it has no id yet.
    */
    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = false
    ) : void;

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function ForgetDaftObjectById($id) : void;

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function RemoveDaftObjectById($id) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function RecallDaftObject($id) : ? DaftObject;

    /**
    * @param mixed ...$args
    *
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface> $type
    *
    * @return static
    */
    public static function DaftObjectRepositoryByType(string $type, ...$args) : self;

    /**
    * @param mixed ...$args
    *
    * @return static
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : self;
}
