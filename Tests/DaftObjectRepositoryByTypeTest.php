<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectNullStub;
use SignpostMarv\DaftObject\DaftObjectNullStubCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;

class DaftObjectRepositoryByTypeTest extends TestCase
{
    public function RepositoryTypeDataProvider() : array
    {
        return [
            [
                DaftObjectMemoryRepository::class,
                DaftObjectNullStub::class,
                DaftObjectCreatedByArray::class,
            ],
            [
                DaftObjectMemoryRepository::class,
                DaftObjectNullStubCreatedByArray::class,
                DefinesOwnIdPropertiesInterface::class,
            ],
            [
                DaftObjectMemoryRepository::class,
                '-foo',
                DaftObjectCreatedByArray::class,
            ],
        ];
    }

    /**
    * @dataProvider RepositoryTypeDataProvider
    */
    public function testForCreatedByArray(
        string $repoImplementation,
        string $typeImplementation,
        string $typeExpected
    ) : void {
        $this->expectException(DaftObjectRepositoryTypeException::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ' .
            $repoImplementation .
            '::DaftObjectRepositoryByType() must be an implementation of ' .
            $typeExpected .
            ', ' .
            $typeImplementation .
            ' given.'
        );

        /**
        * @var DaftObjectRepository $repoImplementation
        */
        $repoImplementation = $repoImplementation;

        $repoImplementation::DaftObjectRepositoryByType($typeImplementation);
    }
}
