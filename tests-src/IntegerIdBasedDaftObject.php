<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;

class IntegerIdBasedDaftObject extends AbstractArrayBackedDaftObject implements DefinesOwnUntypedIdInterface
{
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = [
        'Foo',
    ];

    const EXPORTABLE_PROPERTIES = [
        'Foo',
    ];

    const JSON_PROPERTIES = self::EXPORTABLE_PROPERTIES;

    /**
    * @param array<int|string, scalar|null|array|object> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        if (isset($data['Foo']) && ! is_integer($data['Foo'])) {
            if ( is_string($data['Foo']) && ctype_digit($data['Foo'])) {
                $data['Foo'] = (int) $data['Foo'];
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Value for %s::$Foo is invalid!',
                    static::class
                ));
            }
        }

        parent::__construct($data, $writeAll);
    }

    public function GetFoo() : int
    {
        return (int) $this->RetrievePropertyValueFromData('Foo');
    }

    public function GetId() : int
    {
        return $this->GetFoo();
    }

    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
        ];
    }
}
