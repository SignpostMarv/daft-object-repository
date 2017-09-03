<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

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
                ],
                [
                    'Foo' => '2',
                ],
                [
                    'Foo' => '3',
                ],
                [
                    'Foo' => '4',
                ],
                [
                    'Foo' => '5',
                ],
                [
                    'Foo' => '6',
                ],
                [
                    'Foo' => '7',
                ],
                [
                    'Foo' => '8',
                ],
                [
                    'Foo' => '9',
                ],
                [
                    'Foo' => '10',
                ],
            ],
            [
                ReadWriteTwoColumnPrimaryKey::class,
                true,
                true,
                [
                    'Foo' => '1',
                    'Bar' => 1.0,
                ],
                [
                    'Foo' => '2',
                    'Bar' => 2.0,
                ],
                [
                    'Foo' => '3',
                    'Bar' => 3.0,
                ],
                [
                    'Foo' => '4',
                    'Bar' => 4.0,
                ],
                [
                    'Foo' => '5',
                    'Bar' => 5.0,
                ],
                [
                    'Foo' => '6',
                    'Bar' => 6.0,
                ],
                [
                    'Foo' => '7',
                    'Bar' => 7.0,
                ],
                [
                    'Foo' => '8',
                    'Bar' => 8.0,
                ],
                [
                    'Foo' => '9',
                    'Bar' => 9.0,
                ],
                [
                    'Foo' => '10',
                    'Bar' => 10.0,
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
        $repo = static::DaftObjectRepositoryByType(
            $objImplementation
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

            $repo->RemoveDaftObject($obj);

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
