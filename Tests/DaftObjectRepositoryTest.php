<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Generator;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;

class DaftObjectRepositoryTest extends TestCase
{
    public static function DaftObjectRepositoryByType(string $type) : DaftObjectRepository
    {
        return DaftObjectMemoryRepository::DaftObjectRepositoryByType($type);
    }

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
    * @dataProvider RepositoryDataProvider
    *
    * @psalm-suppress InvalidStringClass
    */
    public function testRepositoryForImplementaion(
        string $objImplementation,
        bool $readable,
        bool $writeable,
        array ...$paramsArray
    ) : void {
        static::assertTrue(
            is_subclass_of(
                $objImplementation,
                DefinesOwnIdPropertiesInterface::class,
                true
            ),
            sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                DefinesOwnIdPropertiesInterface::class
            )
        );

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
            * @var DefinesOwnIdPropertiesInterface
            */
            $obj = new $objImplementation($params, $writeable);

            $repoByObject = static::DaftObjectRepositoryByDaftObject($obj);

            static::assertSame(get_class($repo), get_class($repoByObject));

            $ids = [];

            $repo->RememberDaftObject($obj);

            $props = array_values($idProps);

            foreach ($props as $prop) {
                /**
                * @var scalar|array|\SignpostMarv\DaftObject\DaftObject|null
                */
                $val = $obj->$prop;

                $ids[] = $val;
            }

            if (1 === count($ids)) {
                static::assertSame($obj, $repo->RecallDaftObject($ids[0]));
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
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    *
    * @psalm-suppress InvalidStringClass
    */
    public function testForgetDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        static::assertTrue(is_subclass_of(
            $objectTypeA,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));
        static::assertTrue(is_subclass_of(
            $objectTypeB,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
        $A = new $objectTypeA($dataTypeA);

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
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
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    *
    * @psalm-suppress InvalidStringClass
    */
    public function testRemoveDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        static::assertTrue(is_subclass_of(
            $objectTypeA,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));
        static::assertTrue(is_subclass_of(
            $objectTypeB,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
        $A = new $objectTypeA($dataTypeA);

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
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
    * @dataProvider DaftObjectRepositoryTypeExceptionForgetRemoveDataProvider
    *
    * @psalm-suppress InvalidStringClass
    */
    public function testRememberDaftObjectRepositoryTypeException(
        string $objectTypeA,
        string $objectTypeB,
        array $dataTypeA,
        array $dataTypeB
    ) : void {
        static::assertTrue(is_subclass_of(
            $objectTypeA,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));
        static::assertTrue(is_subclass_of(
            $objectTypeB,
            DefinesOwnIdPropertiesInterface::class,
            true
        ));

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
        $A = new $objectTypeA($dataTypeA);

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
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
    * @psalm-suppress InvalidStringClass
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
        static::assertTrue(is_subclass_of($objImplementation, DaftObject::class, true));

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
                static::assertSame($obj->$prop, $retrieved->$prop);
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
                true === is_numeric($obj->$prop)
            ) {
                /**
                * @var int|float|string|scalar|array|DaftObject\DaftObject|null
                */
                $propVal = $retrieved->$prop;

                if ( ! is_null($propVal) && is_numeric($propVal)) {
                    /**
                    * @var int|float
                    */
                    $propVal = $propVal;

                    $retrieved->$prop = $propVal * 2;
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
    * @psalm-suppress InvalidStringClass
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
        static::assertTrue(
            is_a($retrieved, $objImplementation, true),
            (
                get_class($repo) .
                '::RecallDaftObject() must return an implementation of ' .
                $objImplementation
            )
        );

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
                true === is_numeric($obj->$prop)
            ) {
                /**
                * @var int|float
                */
                $propVal = $obj->$prop;
                static::assertSame($propVal * 2, $retrieved->$prop);
                $retrieved->$prop /= 2;
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
