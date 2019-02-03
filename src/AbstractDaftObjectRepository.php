<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray
*
* @template-implements DaftObjectRepository<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray>
*/
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

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        TypeParanoia::ThrowIfNotDaftObjectIdPropertiesType(
            $object,
            1,
            static::class,
            __FUNCTION__,
            $this->type
        );

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

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        TypeParanoia::ThrowIfNotDaftObjectIdPropertiesType(
            $object,
            1,
            static::class,
            __FUNCTION__,
            $this->type
        );

        /**
        * @var (scalar|array|object|null)[]
        */
        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            /**
            * @var scalar|array|object|null
            */
            $id_val = $object->__get($prop);

            $id[] = $id_val;
        }

        $this->RemoveDaftObjectById($id);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param class-string<T> $type
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        TypeParanoia::ThrowIfNotType(
            $type,
            1,
            static::class,
            __FUNCTION__,
            DaftObjectCreatedByArray::class,
            DefinesOwnIdPropertiesInterface::class
        );

        return new static($type, ...$args);
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param T $object
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : DaftObjectRepository {
        /**
        * @psalm-var class-string<T>
        */
        $className = get_class($object);

        return static::DaftObjectRepositoryByType($className, ...$args);
    }
}
