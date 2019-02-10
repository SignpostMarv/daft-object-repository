<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as SuitableForRepositoryType
*/
interface DaftObjectRepository
{
    /**
    * @psalm-param T $object
    */
    public function RememberDaftObject(SuitableForRepositoryType $object) : void;

    /**
    * Allow data to be persisted without assuming the object exists, i.e. if it has no id yet.
    *
    * @psalm-param T $object
    */
    public function RememberDaftObjectData(
        SuitableForRepositoryType $object,
        bool $assumeDoesNotExist = false
    ) : void;

    /**
    * @psalm-param T $object
    */
    public function ForgetDaftObject(SuitableForRepositoryType $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function ForgetDaftObjectById($id) : void;

    /**
    * @psalm-param T $object
    */
    public function RemoveDaftObject(SuitableForRepositoryType $object) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    */
    public function RemoveDaftObjectById($id) : void;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return T|null
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType;

    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    public function RecallDaftObjectOrThrow(
        $id,
        string $type = SuitableForRepositoryType::class
    ) : SuitableForRepositoryType;

    /**
    * @param mixed ...$args
    *
    * @psalm-param class-string<T> $type
    */
    public static function DaftObjectRepositoryByType(string $type, ...$args) : self;

    /**
    * @param mixed ...$args
    *
    * @psalm-param T $object
    *
    * @return static
    *
    * @psalm-return DaftObjectRepository<T>
    */
    public static function DaftObjectRepositoryByDaftObject(
        SuitableForRepositoryType $object,
        ...$args
    ) : self;
}
