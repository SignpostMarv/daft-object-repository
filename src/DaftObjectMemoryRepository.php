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
* @template-extends AbstractDaftObjectRepository<T>
*/
class DaftObjectMemoryRepository extends AbstractDaftObjectRepository
{
    const BOOL_DEFAULT_ASSUMEDOESNOTEXIST = false;

    /**
    * {@inheritdoc}
    *
    * @psalm-param T $object
    */
    public function RememberDaftObject(SuitableForRepositoryType $object) : void
    {
        $hashId = $object::DaftObjectIdHash($object);

        $this->memory[$hashId] = $object;

        $this->RememberDaftObjectData($object);
    }

    public function ForgetDaftObjectById($id) : void
    {
        $this->ForgetDaftObjectByHashId($this->type::DaftObjectIdValuesHash(
            is_array($id) ? $id : [$id]
        ));
    }

    public function RemoveDaftObjectById($id) : void
    {
        $this->RemoveDaftObjectByHashId($this->type::DaftObjectIdValuesHash(
            is_array($id) ? $id : [$id]
        ));
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-return T|null
    */
    public function RecallDaftObject($id) : ? SuitableForRepositoryType
    {
        $hashId = $this->type::DaftObjectIdValuesHash(
            is_array($id) ? $id : [$id]
        );

        return $this->memory[$hashId] ?? $this->RecallDaftObjectFromData($id);
    }

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
    ) : SuitableForRepositoryType {
        $out = $this->RecallDaftObject($id);

        if (is_null($out) || ! is_a($out, $type, true)) {
            throw DaftObjectRepository\Exceptions\Factory::DaftObjectNotRecalledExceptionWithTypeFromArgumentOnImplementation(
                1,
                DaftObjectRepository::class . '::RecallDaftObjectOrThrow',
                $type,
                static::class
            );
        }

        /**
        * @psalm-var T
        */
        $out = $out;

        return $out;
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param T $object
    */
    public function RememberDaftObjectData(
        SuitableForRepositoryType $object,
        bool $assumeDoesNotExist = self::BOOL_DEFAULT_ASSUMEDOESNOTEXIST
    ) : void {
        $hashId = $object::DaftObjectIdHash($object);

        $this->data[$hashId] = [];

        foreach ($object::DaftObjectPublicGetters() as $property) {
            $this->data[$hashId][$property] = $object->__get($property);
        }
    }

    /**
    * Recalls the corresponding daft object instance from cached data.
    *
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-return T|null
    */
    protected function RecallDaftObjectFromData($id) : ? SuitableForRepositoryType
    {
        $hashId = $this->type::DaftObjectIdValuesHash(
            is_array($id) ? $id : [$id]
        );

        if (isset($this->data[$hashId])) {
            $type = $this->type;

            $out = new $type($this->data[$hashId]);

            return $out;
        }

        return null;
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
