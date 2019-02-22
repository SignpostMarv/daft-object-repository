<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftObjectRepository\Tests\DaftObjectMemoryRepository;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectNotRecalledException;
use SignpostMarv\DaftObject\DaftObjectRepository;
use SignpostMarv\DaftObject\DaftObjectRepository\Tests\SuitableForRepositoryType\Fixtures\SuitableForRepositoryIntType;
use SignpostMarv\DaftObject\SuitableForRepositoryType;
use PHPUnit\Framework\TestCase as Base;

/**
* @template T as SuitableForRepositoryIntType
* @template R as Fixtures\DaftObjectMemoryRepositorySuitableForRepositoryIntType
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
        /**
        * @psalm-var class-string<R>
        */
        $repo_type = static::ObtainDaftObjectRepositoryType();

        $a = static::ObtainSuitableForRepositoryIntTypeFromArgs([
            'id' => 1,
            'foo' => 'bar',
        ]);

        /**
        * @psalm-var R
        */
        $repo = $repo_type::DaftObjectRepositoryByType(
            SuitableForRepositoryIntType::class
        );

        $repo_from_object = $repo_type::DaftObjectRepositoryByDaftObject($a);

        static::assertSame(get_class($repo), get_class($repo_from_object));

        $repo->RememberDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            [
                'foo' => 'bar',
            ]
        );

        $repo->ForgetDaftObjectById(1);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            [
                'foo' => 'bar',
            ]
        );

        $repo->RememberDaftObject($a);

        $repo->ForgetDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            [
                'foo' => 'bar',
            ]
        );

        $repo->RemoveDaftObjectById(1);

        $a_recalled = $repo->RecallDaftObject(1);

        static::assertNull($a_recalled);

        $repo->RememberDaftObject($a);

        $this->RecallThenAssertBothModes(
            $repo,
            $a,
            [
                'foo' => 'bar',
            ]
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
            Fixtures\DaftObjectMemoryRepositorySuitableForRepositoryIntType::class .
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
}
