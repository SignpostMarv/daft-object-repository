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
    use TraitThrowIfNotType;

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

    /**
    * @var mixed[]|null
    */
    protected $args;

    /**
    * @param mixed ...$args
    */
    protected function __construct(string $type, ...$args)
    {
        $this->type = $type;
        $this->args = $args;
        unset($this->args);
    }

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, $this->type, 1, __FUNCTION__);

        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            /**
            * @var scalar|array|object|null
            */
            $val = $object->$prop;

            $id[] = $val;
        }

        $this->ForgetDaftObjectById($id);
    }

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        static::ThrowIfNotType($object, $this->type, 1, __FUNCTION__);

        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            /**
            * @var scalar|array|object|null
            */
            $val = $object->$prop;

            $id[] = $val;
        }

        $this->RemoveDaftObjectById($id);
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        foreach (
            [
                DaftObjectCreatedByArray::class,
                DefinesOwnIdPropertiesInterface::class,
            ] as $checkFor
        ) {
            static::ThrowIfNotType($type, $checkFor, 1, __FUNCTION__);
        }

        return new static($type, ...$args);
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : DaftObjectRepository {
        return static::DaftObjectRepositoryByType(get_class($object), ...$args);
    }
}
