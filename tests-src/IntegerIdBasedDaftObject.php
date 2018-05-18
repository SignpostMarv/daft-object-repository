<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class IntegerIdBasedDaftObject extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'Foo',
    ];

    const EXPORTABLE_PROPERTIES = [
        'Foo',
    ];

    const JSON_PROPERTIES = self::EXPORTABLE_PROPERTIES;

    public function GetFoo() : int
    {
        return $this->RetrievePropertyValueFromData('Foo');
    }
}
