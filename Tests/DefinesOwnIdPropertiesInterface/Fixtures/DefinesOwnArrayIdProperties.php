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
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;

/**
* @property-read array $id
*/
class DefinesOwnArrayIdProperties extends AbstractArrayBackedDaftObject implements DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnIntIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    public function __construct(array $data = ['id' => ['foo' => 1, 'bar' => 2]], bool $writeAll = false)
    {
        if ( ! isset($data['id']) || ! is_array($data['id'])) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' was not array{id:array}!'
            );
        }

        $data = ['id' => $data['id']];

        parent::__construct($data, $writeAll);
    }

    /**
    * {@inheritdoc}
    *
    * @return scalar[]
    */
    public function GetId() : array
    {
        /**
        * @var scalar[]
        */
        $out = $this->RetrievePropertyValueFromData('id');

        return $out;
    }

    public static function DaftObjectIdProperties() : array
    {
        return self::PROPERTIES;
    }
}
