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
*
* @template-implements DaftObjectRepository<T>
*/
abstract class AbstractDaftObjectRepository implements DaftObjectRepository
{
    /**
    * @var array<string, SuitableForRepositoryType>
    *
    * @psalm-var array<string, T>
    */
    protected $memory = [];

    /**
    * @var array<string, array<string, mixed>>
    */
    protected $data = [];

    /**
    * @var string
    *
    * @psalm-var class-string<T>
    */
    protected $type;

    /**
    * @var mixed[]|null
    */
    protected $args;

    /**
    * @param mixed ...$args
    *
    * @psalm-param class-string<T> $type
    */
    protected function __construct(string $type, ...$args)
    {
        $this->type = $type;
        $this->args = $args;
        unset($this->args);
    }

    /**
    * {@inheritdoc}
    */
    public function ForgetDaftObject(SuitableForRepositoryType $object) : void
    {
        /**
        * @var (scalar|array|object|null)[]
        */
        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            /**
            * @var scalar|array|object|null
            */
            $id_val = $object->__get($prop);

            $id[] = $id_val;
        }

        $this->ForgetDaftObjectById($id);
    }

    /**
    * {@inheritdoc}
    */
    public function RemoveDaftObject(SuitableForRepositoryType $object) : void
    {
        /**
        * @var (scalar|array|object|null)[]
        */
        $id = [];

        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            $id_val = $object->__get($prop);

            $id[] = $id_val;
        }

        $this->RemoveDaftObjectById($id);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return AbstractDaftObjectRepository<T>
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        return new static($type, ...$args);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param T $object
    *
    * @psalm-return AbstractDaftObjectRepository<T>
    */
    public static function DaftObjectRepositoryByDaftObject(
        SuitableForRepositoryType $object,
        ...$args
    ) : DaftObjectRepository {
        /**
        * @psalm-var class-string<T>
        */
        $className = get_class($object);

        return static::DaftObjectRepositoryByType($className, ...$args);
    }
}
