<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\EnsureProtectedMethodsNeedToBeProtectedOnRepository;
use SignpostMarv\DaftObject\ReadOnly;

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
}
