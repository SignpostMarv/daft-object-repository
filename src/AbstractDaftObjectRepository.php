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
    * mixed[][].
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

    public function ForgetDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        if (is_a($object, $this->type, true) === false) {
            throw new DaftObjectRepositoryTypeException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __FUNCTION__ .
                '() must be an instance of ' .
                $this->type .
                ', ' .
                get_class($object) .
                ' given.'
            );
        }

        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $id[] = $object->$prop;
        }

        $this->ForgetDaftObjectById($id);
    }

    public function RemoveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        if (is_a($object, $this->type, true) === false) {
            throw new DaftObjectRepositoryTypeException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __FUNCTION__ .
                '() must be an instance of ' .
                $this->type .
                ', ' .
                get_class($object) .
                ' given.'
            );
        }

        $id = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $id[] = $object->$prop;
        }

        $this->RemoveDaftObjectById($id);
    }

    public static function DaftObjectRepositoryByType(
        string $type
    ) : DaftObjectRepository {
        if (
            is_a(
                $type,
                DefinesOwnIdPropertiesInterface::class,
                true
            ) === false
        ) {
            throw new DaftObjectRepositoryTypeException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __FUNCTION__ .
                '() must be an implementation of ' .
                DefinesOwnIdPropertiesInterface::class .
                ', ' .
                $type .
                ' given.'
            );
        }

        return new static($type);
    }

    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository {
        return static::DaftObjectRepositoryByType(get_class($object));
    }
}
