<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class EnsureProtectedMethodsNeedToBeProtectedOnAbstractDaftObject extends AbstractArrayBackedDaftObject
{
    public function EnsureMaybeThrowOnDoGetSet(string $property, bool $setter, array $props) : void
    {
        $this->MaybeThrowOnDoGetSet($property, $setter, $props);
    }
}
