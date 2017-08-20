<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
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
        $repo = DaftObjectMemoryRepository::GetRepositoryForImplementation(
            $objImplementation
        );

        $idProps = [];

        foreach ($objImplementation::DaftObjectIdProperties() as $idProp) {
            $idProps[] = $idProp;
        }

        foreach ($paramsArray as $params) {
            $obj = new $objImplementation($params, $writeable);

            $repoByObject = DaftObjectMemoryRepository::GetRepositoryForDaftObject(
                $obj
            );

            $this->assertSame($repo, $repoByObject);

            $ids = [];

            $repo->RememberDaftObject($obj);

            $props = array_values($idProps);

            foreach ($props as $prop) {
                $ids[] = $obj->$prop;
            }

            if (count($ids) === 1) {
                $this->assertSame($obj, $repo->RetrieveById($ids[0]));

                $repo->UpdateDaftObject($obj);
            }

            $this->assertSame($obj, $repo->RetrieveById($ids));

            if (count($ids) < 1) {
                throw new RuntimeException(
                    'Insufficient Id properties found!'
                );
            }

            $repo->ForgetDaftObject($obj);

            $repo->UpdateDaftObject($obj);

            $repo->RemoveDaftObject($obj);

            $repo->UpdateDaftObject($obj);

            if (count($ids) === 1) {
                $repo->RemoveById($ids[0]);

                $repo->UpdateDaftObject($obj);
            }

            $repo->RemoveById($ids);
        }
    }
}
