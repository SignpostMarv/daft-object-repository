<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObjectRepository;

use Generator;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectNotRecalledException;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use SignpostMarv\DaftObject\Tests\TestCase;

/**
* @template T as SuitableForRepositoryType
* @template TRepo as DaftObjectRepository
*/
class DaftObjectRepositoryTest extends TestCase
{
    /**
    * @psalm-return class-string<TRepo>
    */
    public static function DaftObjectRepositoryClassString() : string
    {
        return DaftObjectMemoryRepository::class;
    }

    /**
    * @psalm-param class-string<T> $type
    *
    * @psalm-return TRepo
    */
    public static function DaftObjectRepositoryByType(string $type) : DaftObjectRepository
    {
        return static::DaftObjectRepositoryClassString()::DaftObjectRepositoryByType($type);
    }

    /**
    * @psalm-param T $object
    *
    * @psalm-return TRepo
    */
    public static function DaftObjectRepositoryByDaftObject(
        SuitableForRepositoryType $object
    ) : DaftObjectRepository {
        return static::DaftObjectRepositoryClassString()::DaftObjectRepositoryByDaftObject($object);
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

    /**
    * @psalm-param class-string<T> $objImplementation
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

        $idProperties = $objImplementation::DaftObjectIdProperties();

        foreach ($idProperties as $idProp) {
            $idProps[] = $idProp;
        }

        foreach ($paramsArray as $params) {
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

            static::assertInstanceOf(SuitableForRepositoryType::class, $obj);

            $repo->ForgetDaftObject($obj);

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
    * @param (scalar|array|object|null)[] $ids
    *
    * @psalm-param class-string<T> $objImplementation
    */
    protected function repositoryForImplementaionTestRetrievedInLoopOne(
        SuitableForRepositoryType $retrieved,
        SuitableForRepositoryType $obj,
        DaftObjectRepository $repo,
        string $objImplementation,
        array $ids,
        array $idProps,
        bool $writeable
    ) : void {
        static::assertSame(get_class($obj), get_class($retrieved));
        static::assertNotSame($obj, $retrieved);

        static::assertSame(
            $objImplementation::DaftObjectIdHash($obj),
            $objImplementation::DaftObjectIdHash($retrieved)
        );

        $properties = $objImplementation::DaftObjectProperties();

        foreach ($properties as $prop) {
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
        * @var SuitableForRepositoryType|null
        *
        * @psalm-var T|null
        */
        $retrieved = $repo->RecallDaftObject($ids);

        if ( ! is_null($retrieved)) {
            $notThrown = $repo->RecallDaftObjectOrThrow($ids);

            static::assertSame(get_class($retrieved), get_class($notThrown));

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
    *
    * @psalm-param T $retrieved
    * @psalm-param T $obj
    * @psalm-param TRepo $repo
    * @psalm-param class-string<T> $objImplementation
    */
    protected function repositoryForImplementaionTestRetrievedInLoopTwo(
        SuitableForRepositoryType $retrieved,
        SuitableForRepositoryType $obj,
        DaftObjectRepository $repo,
        string $objImplementation,
        array $ids,
        array $idProps,
        bool $writeable
    ) : void {
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

        static::expectException(DaftObjectNotRecalledException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            DaftObjectRepository::class .
            '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
            SuitableForRepositoryType::class .
            ' from ' .
            get_class($repo) .
            '::RecallDaftObject()'
        );

        $repo->RecallDaftObjectOrThrow($ids);
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
