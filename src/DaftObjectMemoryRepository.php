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
        static::ThrowIfNotType($object, $this->type, 1, __FUNCTION__);

        $hashId = $object::DaftObjectIdHash($object);

        $this->memory[$hashId] = $object;

        $this->RememberDaftObjectData($object);
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

        if (false === isset($this->memory[$hashId])) {
            return $this->RecallDaftObjectFromData($id);
        }

        return $this->memory[$hashId];
    }

    protected function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object
    ) : void {
        $hashId = $object::DaftObjectIdHash($object);

        $this->data[$hashId] = [];

        foreach ($object::DaftObjectProperties() as $property) {
            $getter = 'Get' . ucfirst($property);

            if (
                true === method_exists($object, $getter) &&
                isset($object->$property)
            ) {
                $this->data[$hashId][$property] = $object->$getter();
            }
        }
    }

    /**
    * Recalls the corresponding daft object instance from cached data.
    *
    * @param mixed $id
    */
    protected function RecallDaftObjectFromData($id) : ? DaftObject
    {
        $hashId = $this->ObjectHashId($id);
        if (true === isset($this->data[$hashId])) {
            $type = $this->type;

            /**
            * @var DaftObjectCreatedByArray $out
            */
            $out = new $type($this->data[$hashId]);

            return $out;
        }

        return null;
    }

    /**
    * Converts an id to a unique-enough-for-now string.
    *
    * @param mixed $id
    */
    private function ObjectHashId($id) : string
    {
        $id = is_array($id) ? $id : [$id];

        /**
        * @var DefinesOwnIdPropertiesInterface $type
        */
        $type = $this->type;

        return $type::DaftObjectIdValuesHash($id);
    }

    private function ForgetDaftObjectByHashId(string $hashId) : void
    {
        if (true === isset($this->memory[$hashId])) {
            unset($this->memory[$hashId]);
        }
    }

    private function RemoveDaftObjectByHashId(string $hashId) : void
    {
        $this->ForgetDaftObjectByHashId($hashId);

        if (true === isset($this->data[$hashId])) {
            unset($this->data[$hashId]);
        }
    }
}
