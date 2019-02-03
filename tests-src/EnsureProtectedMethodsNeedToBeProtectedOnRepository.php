<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class EnsureProtectedMethodsNeedToBeProtectedOnRepository extends DaftObjectMemoryRepository
{
    /**
    * @param scalar|array<string, scalar|null> $id
    */
    public function EnsureRecallDaftObjectFromData($id) : ? DefinesOwnIdPropertiesInterface
    {
        return $this->RecallDaftObjectFromData($id);
    }

    /**
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $type
    */
    public static function EnsureConstructorNeedsToBeProtected(
        string $type,
        ...$args
    ) : AbstractDaftObjectRepository {
        return new static($type, ...$args);
    }
}
