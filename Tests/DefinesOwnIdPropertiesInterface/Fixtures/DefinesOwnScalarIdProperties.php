<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DefinesOwnIdPropertiesInterface\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;

/**
* @template T as scalar
*
* @property-read scalar $id
*/
class DefinesOwnScalarIdProperties extends AbstractArrayBackedDaftObject implements DefinesOwnIdPropertiesInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnScalarIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    public function __construct(array $data = ['id' => 0], bool $writeAll = false)
    {
        if ( ! isset($data['id']) || ! is_scalar($data['id'])) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' was not array{id:scalar}!'
            );
        }

        /**
        * @psalm-var array{id:T}
        */
        $data = $data;

        parent::__construct($data, $writeAll);
    }

    /**
    * @return scalar
    *
    * @psalm-return T
    */
    public function GetId()
    {
        /**
        * @var scalar
        *
        * @psalm-var T
        */
        $out = $this->RetrievePropertyValueFromData('id');

        return $out;
    }

    public static function DaftObjectIdProperties() : array
    {
        return self::PROPERTIES;
    }
}
