<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\ReadWrite;

class DaftObjectRepositoryTest extends TestCase
{
    public function RepositoryDataProvider() : array
    {
        return [
            [
                DaftObjectMemoryRepository::class,
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
        ];
    }

    public function DaftObjectRepositoryTypeExceptionByTypeDataProvider(
    ) : array {
        return [
            [
                DaftObjectMemoryRepository::class,
            ],
        ];
    }

    public function DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider(
    ) : array {
        return [
            [
                DaftObjectMemoryRepository::class,
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
        string $repoImplementation,
        string $objImplementation,
        bool $readable,
        bool $writeable,
        array ...$paramsArray
    ) : void {
        $repo = DaftObjectMemoryRepository::DaftObjectRepositoryByType(
            $objImplementation
        );

        $idProps = [];

        foreach ($objImplementation::DaftObjectIdProperties() as $idProp) {
            $idProps[] = $idProp;
        }

        foreach ($paramsArray as $params) {
            $obj = new $objImplementation($params, $writeable);

            $repoByObject = DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject(
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

    /**
    * @dataProvider DaftObjectRepositoryTypeExceptionByTypeDataProvider
    */
    public function testRepositoryForDaftObjectRepositoryTypeException(
        string $implementation
    ) : void {
        static $type = '-foo';

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

        $implementation::DaftObjectRepositoryByType($type);
    }

    /**
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    */
    public function testForgetDaftObjectRepositoryTypeException(
        string $repoImplementation,
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = $repoImplementation::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            $repoImplementation .
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
        string $repoImplementation,
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = $repoImplementation::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            $repoImplementation .
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
        string $repoImplementation,
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        $A = new $objectTypeA($dataTypeA);
        $B = new $objectTypeB($dataTypeB);

        $repo = $repoImplementation::DaftObjectRepositoryByDaftObject($A);

        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            $repoImplementation .
            '::RememberDaftObject() must be an instance of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->RememberDaftObject($B);
    }
}
