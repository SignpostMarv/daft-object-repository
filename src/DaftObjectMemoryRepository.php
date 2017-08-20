<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class DaftObjectMemoryRepository implements DaftObjectRepository
{
    /**
    * @var DefinesOwnIdPropertiesInterface[]
    */
    private $memory = [];

    /**
    * @var string
    */
    private $implementation;

    protected function __construct(string $implementation)
    {
        $this->implementation = $implementation;
    }

    public function RememberDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        if (is_a($object, $this->implementation, true) === false) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __METHOD__ .
                '() must be of type ' .
                $this->implementation .
                ', not ' .
                get_class($object)
            );
        }

        $memory = &$this->memory;
        $props = $object::DaftObjectIdProperties();
        $lastProp = array_pop($props);

        foreach ($props as $prop) {
            if (isset($memory[$object->$prop]) === false) {
                $memory[$object->$prop] = [];
            }

            $memory = &$memory[$object->$prop];
        }

        $memory[$object->$lastProp] = $object;
    }

    public function RememberAllNotForgotten() : void
    {
    }

    public function RetrieveById($id) : DefinesOwnIdPropertiesInterface
    {
        $memory = $this->memory;

        if (is_array($id)) {
            $lastVal = array_pop($id);

            foreach ($id as $val) {
                $memory = $memory[$val];
            }
        } else {
            $lastVal = $id;
        }

        return $memory[$lastVal];
    }

    public function RetrieveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DefinesOwnIdPropertiesInterface {
        $idVal = $this->IdPropValsFromType($object);

        return $this->RetrieveById($idVal);
    }

    public function ForgetById($id) : void
    {
        $memory = &$this->memory;

        if (is_array($id)) {
            $lastVal = array_pop($id);

            foreach ($id as $val) {
                $memory = &$memory[$val];
            }
        } else {
            $lastVal = $id;
        }

        unset($memory[$lastVal]);
    }

    public function ForgetDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        $idVal = $this->IdPropValsFromType($object);

        $this->ForgetById($idVal);
    }

    public function RemoveById($id) : void
    {
        $this->ForgetById($id);
    }

    public function RemoveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        $this->ForgetDaftObject($object);
    }

    public function UpdateDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        $this->RememberDaftObject($object);
    }

    public static function GetRepositoryForDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository {
        return static::GetRepositoryForImplementation(get_class($object));
    }

    public static function GetRepositoryForImplementation(
        string $implementation
    ) : DaftObjectRepository {
        static $repositories = [];

        if (
            is_a(
                $implementation,
                DefinesOwnIdPropertiesInterface::class,
                false
            )
        ) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __METHOD__ .
                '() must be of type ' .
                DefinesOwnIdPropertiesInterface::class .
                ', not ' .
                $implementation
            );
        } elseif (isset($repositories[$implementation]) === false) {
            $repositories[$implementation] = new static($implementation);
        }

        return $repositories[$implementation];
    }

    private function IdPropValsFromType(
        DefinesOwnIdPropertiesInterface $object
    ) : array {
        if (is_a($object, $this->implementation, true) === false) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                static::class .
                '::' .
                __METHOD__ .
                '() must be of type ' .
                $this->implementation .
                ', not ' .
                get_class($object)
            );
        }

        $idVal = [];

        foreach ($object::DaftObjectIdProperties() as $prop) {
            $idVal[] = $object->$prop;
        }

        return $idVal;
    }
}
