<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObjectRepository;

use Generator;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\Tests\TestCase;

class DaftObjectRepositoryTest extends TestCase
{
    /**
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $type
    */
    public static function DaftObjectRepositoryByType(string $type) : DaftObjectRepository
    {
        return DaftObjectMemoryRepository::DaftObjectRepositoryByType($type);
    }

    /**
    * @psalm-param DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray $object
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository {
        return DaftObjectMemoryRepository::DaftObjectRepositoryByDaftObject($object);
    }

    public function RepositoryDataProvider() : Generator
    {
        $arrayParams = $this->RepositoryDataProviderParams();
        foreach (
            [
                ReadWrite::class,
                ReadWriteTwoColumnPrimaryKey::class,
            ] as $className
        ) {
            yield array_merge(
                [
                    $className,
                    true,
                    true,
                ],
                $arrayParams
            );
        }
    }

    public function DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider() : array
    {
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
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objImplementation
    *
    * @dataProvider RepositoryDataProvider
    */
    public function testRepositoryForImplementaion(
        string $objImplementation,
        bool $readable,
        bool $writeable,
        array ...$paramsArray
    ) : void {
        $repo = static::DaftObjectRepositoryByType($objImplementation);

        $idProps = [];

        /**
        * @var array<int, string>
        */
        $idProperties = $objImplementation::DaftObjectIdProperties();

        foreach ($idProperties as $idProp) {
            $idProps[] = $idProp;
        }

        foreach ($paramsArray as $params) {
            /**
            * @var DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray
            */
            $obj = new $objImplementation($params, $writeable);

            $repoByObject = static::DaftObjectRepositoryByDaftObject($obj);

            static::assertSame(get_class($repo), get_class($repoByObject));

            /**
            * @var (scalar|array|object|null)[]
            */
            $ids = [];

            $repo->RememberDaftObject($obj);

            $props = array_values($idProps);

            foreach ($props as $prop) {
                /**
                * @var scalar|array|object|null
                */
                $id_val = $obj->__get($prop);

                $ids[] = $id_val;
            }

            if (1 === count($ids)) {
                static::assertIsScalar($ids[0]);

                /**
                * @var scalar
                */
                $id = $ids[0];

                static::assertSame($obj, $repo->RecallDaftObject($id));
            }

            static::assertSame($obj, $repo->RecallDaftObject($ids));

            if (count($ids) < 1) {
                throw new RuntimeException('Insufficient Id properties found!');
            }

            $repo->ForgetDaftObject($obj);

            /**
            * @var DefinesOwnIdPropertiesInterface|null
            */
            $retrieved = $repo->RecallDaftObject($ids);

            if ( ! is_null($retrieved)) {
                $this->repositoryForImplementaionTestRetrievedInLoopOne(
                    $retrieved,
                    $obj,
                    $repo,
                    $objImplementation,
                    $ids,
                    $idProps,
                    $writeable
                );
            }
        }
    }

    /**
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeA
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeB
    *
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
            '::ForgetDaftObject() must be an implementation of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->ForgetDaftObject($B);
    }

    /**
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeA
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeB
    *
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
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to ' .
            get_class($repo) .
            '::RemoveDaftObject() must be an implementation of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        ));

        $repo->RemoveDaftObject($B);
    }

    /**
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeA
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface&DaftObjectCreatedByArray> $objectTypeB
    *
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
            '::RememberDaftObject() must be an implementation of ' .
            $objectTypeA .
            ', ' .
            $objectTypeB .
            ' given.'
        );

        $repo->RememberDaftObject($B);
    }

    /**
    * @param (scalar|array|object|null)[] $ids
    */
    protected function repositoryForImplementaionTestRetrievedInLoopOne(
        DefinesOwnIdPropertiesInterface $retrieved,
        DefinesOwnIdPropertiesInterface $obj,
        DaftObjectRepository $repo,
        string $objImplementation,
        array $ids,
        array $idProps,
        bool $writeable
    ) : void {
        if ( ! is_subclass_of($objImplementation, DefinesOwnIdPropertiesInterface::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DefinesOwnIdPropertiesInterface::class
            );

            return;
        }

        static::assertSame(get_class($obj), get_class($retrieved));
        static::assertNotSame($obj, $retrieved);

        static::assertSame(
            $objImplementation::DaftObjectIdHash($obj),
            $objImplementation::DaftObjectIdHash($retrieved)
        );

        /**
        * @var array<int, string>
        */
        $properties = $objImplementation::DaftObjectProperties();

        foreach ($properties as $prop) {
            /**
            * @var string
            */
            $expectedMethod = static::MethodNameFromProperty($prop);

            if (
                true === method_exists($obj, $expectedMethod) &&
                true === method_exists($retrieved, $expectedMethod)
            ) {
                static::assertSame($obj->__get($prop), $retrieved->__get($prop));
            }
        }

        $repo->RemoveDaftObject($obj);

        static::assertNull($repo->RecallDaftObject($ids));

        foreach ($properties as $prop) {
            $setter = static::MethodNameFromProperty($prop, true);
            $getter = static::MethodNameFromProperty($prop);

            if (
                $writeable &&
                false === in_array($prop, $idProps, true) &&
                true === method_exists($obj, $setter) &&
                true === method_exists($retrieved, $getter) &&
                true === is_numeric($obj->__get($prop))
            ) {
                $propVal = $retrieved->__get($prop);

                if ( ! is_null($propVal) && is_numeric($propVal)) {
                    /**
                    * @var string|int|float
                    */
                    $propVal = $propVal;

                    $propVal =
                        (is_int($propVal) || is_float($propVal))
                            ? $propVal
                            : (
                                ctype_digit($propVal)
                                    ? (int) $propVal
                                    : (float) $propVal
                            );

                    $retrieved->__set($prop, $propVal * 2);
                }
            }
        }

        $repo->RememberDaftObject($retrieved);
        $repo->ForgetDaftObject($obj);
        $repo->ForgetDaftObject($retrieved);

        /**
        * @var DefinesOwnIdPropertiesInterface|null
        */
        $retrieved = $repo->RecallDaftObject($ids);

        if ( ! is_null($retrieved)) {
            $this->repositoryForImplementaionTestRetrievedInLoopTwo(
                $retrieved,
                $obj,
                $repo,
                $objImplementation,
                $ids,
                $idProps,
                $writeable
            );
        }
    }

    /**
    * @param (scalar|array|object|null)[] $ids
    */
    protected function repositoryForImplementaionTestRetrievedInLoopTwo(
        DefinesOwnIdPropertiesInterface $retrieved,
        DefinesOwnIdPropertiesInterface $obj,
        DaftObjectRepository $repo,
        string $objImplementation,
        array $ids,
        array $idProps,
        bool $writeable
    ) : void {
        if ( ! is_subclass_of($objImplementation, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        /**
        * @var array<int, string>
        */
        $properties = $objImplementation::DaftObjectProperties();

        foreach ($properties as $prop) {
            $setter = static::MethodNameFromProperty($prop, true);
            $getter = static::MethodNameFromProperty($prop);

            if (
                $writeable &&
                false === in_array($prop, $idProps, true) &&
                true === method_exists($obj, $setter) &&
                true === method_exists($retrieved, $getter) &&
                true === is_numeric($obj->__get($prop))
            ) {
                /**
                * @var int|float
                */
                $propVal = $obj->__get($prop);
                static::assertSame($propVal * 2, $retrieved->__get($prop));
                $retrieved->__set($prop, $propVal);
            }
        }

        $repo->RememberDaftObject($retrieved);

        $repo->RemoveDaftObject($retrieved);

        static::assertNull($repo->RecallDaftObject($ids));
    }

    protected function RepositoryDataProviderParams() : array
    {
        return [
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
        ];
    }
}
