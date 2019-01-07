<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\EnsureProtectedMethodsNeedToBeProtectedOnAbstractDaftObject;
use SignpostMarv\DaftObject\EnsureProtectedMethodsNeedToBeProtectedOnRepository;
use SignpostMarv\DaftObject\PHPStan\EnsurePropertyReflectionExtensionMethodsNeedToBeProtected;
use SignpostMarv\DaftObject\ReadOnly;
use SignpostMarv\DaftObject\UndefinedPropertyException;
use TypeError;

class EnsureProtectedMethodsNeedToBeProtectedTest extends TestCase
{
    public function testEnsureRecallDaftObjectFromData() : void
    {
        /**
        * @var EnsureProtectedMethodsNeedToBeProtectedOnRepository
        */
        $repo = EnsureProtectedMethodsNeedToBeProtectedOnRepository::DaftObjectRepositoryByType(
            ReadOnly::class
        );
        static::assertNull($repo->EnsureRecallDaftObjectFromData(1));
    }

    public function testEnsureConstructorNeedsToBeProtected() : void
    {
        /**
        * @var EnsureProtectedMethodsNeedToBeProtectedOnRepository
        */
        $repo = EnsureProtectedMethodsNeedToBeProtectedOnRepository::EnsureConstructorNeedsToBeProtected(
            ReadOnly::class
        );
        static::assertNull($repo->EnsureRecallDaftObjectFromData(1));
    }

    public function testEnsureMaybeThrowOnDoGetSet() : void
    {
        $obj = new EnsureProtectedMethodsNeedToBeProtectedOnAbstractDaftObject();

        static::expectException(UndefinedPropertyException::class);

        $obj->EnsureMaybeThrowOnDoGetSet('foo', true, []);
    }

    public function testPropertyIsPublic() : void
    {
        static::assertTrue(EnsurePropertyReflectionExtensionMethodsNeedToBeProtected::EnsurePropertyIsPublic(
            ReadOnly::class,
            'Foo'
        ));
        static::assertFalse(EnsurePropertyReflectionExtensionMethodsNeedToBeProtected::EnsurePropertyIsPublic(
            TypeError::class,
            'Foo'
        ));
    }
}
