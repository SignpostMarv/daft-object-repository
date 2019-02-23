<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DaftObjectMemoryRepository;

use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftObject\DaftObjectMemoryRepository;
use SignpostMarv\DaftObject\DaftObjectNotRecalledException;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepository\Tests\SuitableForRepositoryType\Fixtures\SuitableForRepositoryIntType;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as SuitableForRepositoryIntType
* @template R as DaftObjectMemoryRepository
*/
class DaftObjectMemoryRepositoryTest extends Base
{
    /**
    * @var bool
    */
    protected $backupGlobals = false;

    /**
    * @var bool
    */
    protected $backupStaticAttributes = false;

    /**
    * @var bool
    */
    protected $runTestInSeparateProcess = false;

    public function test_DaftObjectMemoryRepository() : void
    {
        $expected_data = static::InitialData_test_DaftObjectMemoryRepository();

        /**
        * @var SuitableForRepositoryIntType
        *
        * @psalm-var T
        */
        $a = static::ObtainSuitableForRepositoryIntTypeFromArgs(array_merge(
            ['id' => 1],
            $expected_data
        ));

        static::assertInstanceOf(static::ObtainDaftObjectType(), $a);

        /**
        * @psalm-var R
        */
        $repo = $this->ObtainDaftObjectRepositoryAndAssertSameByObject($a);

        $repo->RememberDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        $repo->ForgetDaftObjectById(1);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        $repo->RemoveDaftObject($a);

        $a_recalled = $repo->RecallDaftObject(1);

        static::assertNull($a_recalled);

        $repo->RememberDaftObjectData($a, true);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        foreach (static::ChangedData_test_DaftObjectMemoryRepository() as $k => $v) {
            $expected_data[$k] = $v;

            $a->__set($k, $v);

            static::assertSame($v, $a->__get($k));
        }

        $repo->RememberDaftObjectData($a, false);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        $repo->ForgetDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        $repo->RemoveDaftObjectById(1);

        $a_recalled = $repo->RecallDaftObject(1);

        static::assertNull($a_recalled);

        $repo->RememberDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            $expected_data
        );

        $repo->RemoveDaftObject($a);

        $a_recalled = $repo->RecallDaftObject(1);

        static::assertNull($a_recalled);

        static::expectException(DaftObjectNotRecalledException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            DaftObjectRepository::class .
            '::RecallDaftObjectOrThrow() did not resolve to an instance of ' .
            SuitableForRepositoryType::class .
            ' from ' .
            static::ObtainDaftObjectRepositoryType() .
            '::RecallDaftObject()'
        );

        $repo->RecallDaftObjectOrThrow(1);
    }

    /**
    * @param array<string, scalar|array|object|null> $assert_same_props
    *
    * @psalm-param R $repo
    * @psalm-param T $obj
    */
    protected function RecallThenAssert(
        DaftObjectRepository $repo,
        SuitableForRepositoryType $obj,
        array $assert_same_props,
        bool $recall_not_throw
    ) : void {
        $obj_recalled =
            $recall_not_throw
                ? $repo->RecallDaftObject($obj->id)
                : $repo->RecallDaftObjectOrThrow($obj->id);

        static::assertInstanceOf(static::ObtainDaftObjectType(), $obj_recalled);

        foreach ($assert_same_props as $k => $v) {
            static::assertSame($v, $obj->__get($k));
        }
    }

    /**
    * @param array<string, scalar|array|object|null> $assert_same_props
    *
    * @psalm-param R $repo
    * @psalm-param T $obj
    */
    protected function RecallThenAssertBothModes(
        DaftObjectRepository $repo,
        SuitableForRepositoryType $obj,
        array $assert_same_props
    ) : void {
        $this->RecallThenAssert($repo, $obj, $assert_same_props, false);
        $this->RecallThenAssert($repo, $obj, $assert_same_props, true);
    }

    /**
    * @psalm-return class-string<T>
    */
    protected static function ObtainDaftObjectType() : string
    {
        return SuitableForRepositoryIntType::class;
    }

    /**
    * @psalm-return class-string<R>
    */
    protected static function ObtainDaftObjectRepositoryType() : string
    {
        return Fixtures\DaftObjectMemoryRepositorySuitableForRepositoryIntType::class;
    }

    /**
    * @psalm-return T
    */
    protected static function ObtainSuitableForRepositoryIntTypeFromArgs(
        array $args
    ) : SuitableForRepositoryType {
        $type = static::ObtainDaftObjectType();

        return new $type($args);
    }

    /**
    * @psalm-param T $object
    *
    * @psalm-return R
    */
    protected function ObtainDaftObjectRepositoryAndAssertSameByObject(
        SuitableForRepositoryType $object
    ) : DaftObjectRepository {
        /**
        * @psalm-var class-string<R>
        */
        $repo_type = static::ObtainDaftObjectRepositoryType();

        /**
        * @psalm-var R
        */
        $repo = $repo_type::DaftObjectRepositoryByType(static::ObtainDaftObjectType());

        $repo_from_object = $repo_type::DaftObjectRepositoryByDaftObject($object);

        static::assertSame(get_class($repo), get_class($repo_from_object));

        return $repo;
    }

    /**
    * @return array<string, scalar|array|object|null>
    */
    protected static function InitialData_test_DaftObjectMemoryRepository() : array
    {
        return [
            'foo' => 'bar',
        ];
    }

    /**
    * @return array<string, scalar|array|object|null>
    */
    protected static function ChangedData_test_DaftObjectMemoryRepository() : array
    {
        return [
            'foo' => 'baz',
        ];
    }
}
