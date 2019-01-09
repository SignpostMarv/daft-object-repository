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
    * @param mixed ...$additionalArgs
    *
    * @dataProvider RepositoryTypeDataProvider
    */
    public function testForCreatedByArray(
        string $repoImplementation,
        string $typeImplementation,
        string $typeExpected,
        ...$additionalArgs
    ) : void {
        if ( ! is_subclass_of($repoImplementation, DaftObjectRepository::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObjectRepository::class
            );

            return;
        }

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

        array_unshift($additionalArgs, $typeImplementation);

        /**
        * @var array{0:string}
        */
        $additionalArgs = $additionalArgs;

        $repoImplementation::DaftObjectRepositoryByType(...$additionalArgs);
    }
}
