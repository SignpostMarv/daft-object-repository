<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;

class DaftObjectRepositoryTest extends TestCase
{
    public static function DaftObjectRepositoryByType(
        string $type
    ) : DaftObjectRepository {
        return DaftObjectMemoryRepository::DaftObjectRepositoryByType(
            $type
        );
    }

    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository {
        return DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject(
            $object
        );
    }

    public function RepositoryDataProvider() : array
    {
        return [
            [
                ReadWrite::class,
                true,
                true,
                [
                    'Foo' => '1',
                    'Bar' => 1.0,
                    'Baz' => 1,
                ],
                [
                    'Foo' => '2',
                    'Bar' => 2.0,
                    'Baz' => 2,
                ],
                [
                    'Foo' => '3',
                    'Bar' => 3.0,
                    'Baz' => 3,
                ],
                [
                    'Foo' => '4',
                    'Bar' => 4.0,
                    'Baz' => 4,
                ],
                [
                    'Foo' => '5',
                    'Bar' => 5.0,
                    'Baz' => 5,
                ],
                [
                    'Foo' => '6',
                    'Bar' => 6.0,
                    'Baz' => 6,
                ],
                [
                    'Foo' => '7',
                    'Bar' => 7.0,
                    'Baz' => 7,
                ],
                [
                    'Foo' => '8',
                    'Bar' => 8.0,
                    'Baz' => 8,
                ],
                [
                    'Foo' => '9',
                    'Bar' => 9.0,
                    'Baz' => 9,
                ],
                [
                    'Foo' => '10',
                    'Bar' => 10.0,
                    'Baz' => 10,
                ],
            ],
            [
                ReadWriteTwoColumnPrimaryKey::class,
                true,
                true,
                [
                    'Foo' => '1',
                    'Bar' => 1.0,
                    'Baz' => 1,
                ],
                [
                    'Foo' => '2',
                    'Bar' => 2.0,
                    'Baz' => 2,
                ],
                [
                    'Foo' => '3',
                    'Bar' => 3.0,
                    'Baz' => 3,
                ],
                [
                    'Foo' => '4',
                    'Bar' => 4.0,
                    'Baz' => 4,
                ],
                [
                    'Foo' => '5',
                    'Bar' => 5.0,
                    'Baz' => 5,
                ],
                [
                    'Foo' => '6',
                    'Bar' => 6.0,
                    'Baz' => 6,
                ],
                [
                    'Foo' => '7',
                    'Bar' => 7.0,
                    'Baz' => 7,
                ],
                [
                    'Foo' => '8',
                    'Bar' => 8.0,
                    'Baz' => 8,
                ],
                [
                    'Foo' => '9',
                    'Bar' => 9.0,
                    'Baz' => 9,
                ],
                [
                    'Foo' => '10',
                    'Bar' => 10.0,
                    'Baz' => 10,
                ],
            ],
        ];
    }

    public function DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider(
    ) : array {
        return [
            [
                ReadWrite::class,
                ReadOnly::class,
                [
                    'Foo' => '1',
                ],
                [
                    'Foo' => '1',
                ],
            ],
        ];
    }

    /**
    * @dataProvider RepositoryDataProvider
    */
    public function testRepositoryForImplementaion(
        string $objImplementation,
        bool $readable,
        bool $writeable,
        array ...$paramsArray
    ) : void {
        if (
            is_a(
                $objImplementation,
                DefinesOwnIdPropertiesInterface::class,
                true
            ) === false
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 passed to %s must be an implementation of %s',
                    __METHOD__,
                    DefinesOwnIdPropertiesInterface::class
                )
            );
        }

        /**
        * @var DefinesOwnIdPropertiesInterface $objImplementation
        */
        $objImplementation = $objImplementation;

        $repo = static::DaftObjectRepositoryByType(
            (string) $objImplementation
        );

        $idProps = [];

        foreach ($objImplementation::DaftObjectIdProperties() as $idProp) {
            $idProps[] = $idProp;
        }

        foreach ($paramsArray as $params) {
            $obj = new $objImplementation($params, $writeable);

            $repoByObject = static::DaftObjectRepositoryByDaftObject(
                $obj
            );

            $this->assertSame(get_class($repo), get_class($repoByObject));

            $ids = [];

            $repo->RememberDaftObject($obj);

            $props = array_values($idProps);

            foreach ($props as $prop) {
                $ids[] = $obj->$prop;
            }

            if (count($ids) === 1) {
                $this->assertSame($obj, $repo->RecallDaftObject($ids[0]));
            }

            $this->assertSame($obj, $repo->RecallDaftObject($ids));

            if (count($ids) < 1) {
                throw new RuntimeException(
                    'Insufficient Id properties found!'
                );
            }

            $repo->ForgetDaftObject($obj);

            $retrieved = $repo->RecallDaftObject($ids);

            $this->assertNotNull($retrieved);

            /**
            * @var DefinesOwnIdPropertiesInterface $retrieved
            */
            $retrieved = $retrieved;

            $this->assertSame(
                $objImplementation::DaftObjectIdHash($obj),
                $objImplementation::DaftObjectIdHash($retrieved)
            );
            $this->assertSame(get_class($obj), get_class($retrieved));
            $this->assertNotSame($obj, $retrieved);

            foreach ($objImplementation::DaftObjectProperties() as $prop) {
                if (
                    method_exists($obj, 'Get' . ucfirst($prop)) === true &&
                    method_exists($retrieved, 'Get' . ucfirst($prop)) === true
                ) {
                    $this->assertSame($obj->$prop, $retrieved->$prop);
                }
            }

            $repo->RemoveDaftObject($obj);

            $this->assertNull($repo->RecallDaftObject($ids));

            foreach ($objImplementation::DaftObjectProperties() as $prop) {
                if (
                    in_array($prop, $idProps, true) === false &&
                    method_exists($obj, 'Set' . ucfirst($prop)) === true &&
                    method_exists(
                        $retrieved, 'Get' . ucfirst($prop)
                    ) === true &&
                    is_numeric($obj->$prop) === true
                ) {
                    $retrieved->$prop *= 2;
                }
            }

            $repo->RememberDaftObject($retrieved);
            $repo->ForgetDaftObject($obj);
            $repo->ForgetDaftObject($retrieved);

            /**
            * @var DefinesOwnIdPropertiesInterface $retrieved
            */
            $retrieved = $repo->RecallDaftObject($ids);

            $this->assertInstanceOf((string) $objImplementation, $retrieved);

            foreach ($objImplementation::DaftObjectProperties() as $prop) {
                if (
                    in_array($prop, $idProps, true) === false &&
                    method_exists($obj, 'Set' . ucfirst($prop)) === true &&
                    method_exists(
                        $retrieved, 'Get' . ucfirst($prop)
                    ) === true &&
                    is_numeric($obj->$prop) === true
                ) {
                    $this->assertSame($obj->$prop * 2, $retrieved->$prop);
                    $retrieved->$prop /= 2;
                }
            }

            $repo->RememberDaftObject($retrieved);

            $repo->RemoveDaftObject($retrieved);

            $this->assertNull($repo->RecallDaftObject($ids));
        }
    }

    public function testRepositoryForDaftObjectRepositoryTypeException(
    ) : void {
        static $type = '-foo';

        $implementation = get_class(
            static::DaftObjectRepositoryByType(ReadOnly::class)
        );

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            $implementation .
            '::DaftObjectRepositoryByType() must be an implementation of ' .
            DefinesOwnIdPropertiesInterface::class .
            ', ' .
            $type .
            ' given.'
        );

        static::DaftObjectRepositoryByType($type);
    }

    /**
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    */
    public function testForgetDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = static::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            get_class($repo) .
            '::ForgetDaftObject() must be an instance of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->ForgetDaftObject($B);
    }

    /**
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    */
    public function testRemoveDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = static::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            get_class($repo) .
            '::RemoveDaftObject() must be an instance of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->RemoveDaftObject($B);
    }

    /**
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    */
    public function testRememberDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = static::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            get_class($repo) .
            '::RememberDaftObject() must be an instance of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->RememberDaftObject($B);
    }
}
