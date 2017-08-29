<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class DaftObjectMemoryRepository extends AbstractDaftObjectRepository
{
    /**
    * @var DefinesOwnIdPropertiesInterface[]
    */
    protected $memory = [];

    /**
    * mixed[][].
    */
    protected $data = [];

    public function RememberDaftObject(
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

        $hashId = $object::DaftObjectIdHash($object);

        $this->memory[$hashId] = $object;

        if (isset($this->data[$hashId]) === false) {
            $this->data[$hashId] = [];
        }

        foreach ($object::DaftObjectProperties() as $property) {
            $getter = 'Get' . ucfirst($property);

            if (
                method_exists($object, $getter) === true &&
                isset($object->$property)
            ) {
                $this->data[$hashId][$property] = $object->$getter();
            }
        }
    }

    private function ObjectHashId($id) : string
    {
        $id = is_array($id) ? $id : [$id];

        $type = $this->type;

        return $type::DaftObjectIdValuesHash($id);
    }

    public function ForgetDaftObjectById($id) : void
    {
        $this->ForgetDaftObjectByHashId($this->ObjectHashId($id));
    }

    public function RemoveDaftObjectById($id) : void
    {
        $this->RemoveDaftObjectByHashId($this->ObjectHashId($id));
    }

    public function RecallDaftObject($id) : ? DaftObject
    {
        $hashId = $this->ObjectHashId($id);

        if (isset($this->memory[$hashId]) === false) {
            if (isset($this->data[$hashId]) === true) {
                $type = $this->type;

                return new $type($this->data[$hashId]);
            }

            return null;
        }

        return $this->memory[$hashId];
    }

    private function ForgetDaftObjectByHashId(string $hashId) : void
    {
        if (isset($this->memory[$hashId]) === true) {
            unset($this->memory[$hashId]);
        }
    }

    private function RemoveDaftObjectByHashId(string $hashId) : void
    {
        $this->ForgetDaftObjectByHashId($hashId);

        if (isset($this->data[$hashId]) === true) {
            unset($this->data[$hashId]);
        }
    }
}
