<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractDaftObjectRepository implements DaftObjectRepository
{
    /**
    * @var DefinesOwnIdPropertiesInterface[]
    */
    protected $memory = [];

    /**
    * @var array<string, array<string, mixed>>
    */
    protected $data = [];

    /**
    * @var string
    */
    protected $type;

    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, $this->type, 1, __FUNCTION__);

        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $id[] = $object->$prop;
        }

        $this->ForgetDaftObjectById($id);
    }

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, $this->type, 1, __FUNCTION__);

        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $id[] = $object->$prop;
        }

        $this->RemoveDaftObjectById($id);
    }

    public static function DaftObjectRepositoryByType(string $type) : DaftObjectRepository
    {
        foreach (
            [
                DaftObjectCreatedByArray::class,
                DefinesOwnIdPropertiesInterface::class,
            ] as $checkFor
        ) {
            static::ThrowIfNotType($type, $checkFor, 1, __FUNCTION__);
        }

        return new static($type);
    }

    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository {
        return static::DaftObjectRepositoryByType(get_class($object));
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) : void {
        if (false === is_a($object, $type, is_string($object))) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                $argument,
                static::class,
                $function,
                $type,
                is_string($object) ? $object : get_class($object)
            );
        }
    }
}
