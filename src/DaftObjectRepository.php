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
interface DaftObjectRepository
{
    /**
    * @psalm-param T $object
    */
    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * Allow data to be persisted without assuming the object exists, i.e. if it has no id yet.
    *
    * @psalm-param T $object
    */
    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = false
    ) : void;

    /**
    * @psalm-param T $object
    */
    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function ForgetDaftObjectById($id) : void;

    /**
    * @psalm-param T $object
    */
    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function RemoveDaftObjectById($id) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return T|null
    */
    public function RecallDaftObject($id) : ? DefinesOwnIdPropertiesInterface;

    /**
    * @param mixed ...$args
    *
    * @psalm-param class-string<T> $type
    *
    * @return static
    */
    public static function DaftObjectRepositoryByType(string $type, ...$args) : self;

    /**
    * @param mixed ...$args
    *
    * @psalm-param T $object
    *
    * @return static
    *
    * @psalm-return static<T>
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : self;
}
