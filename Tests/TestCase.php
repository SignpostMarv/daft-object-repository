<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftObject\TypeUtilities;

class TestCase extends Base
{
    const MIN_EXPECTED_ARRAY_COUNT = 2;
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

    public static function MethodNameFromProperty(string $prop, bool $SetNotGet = false) : string
    {
        return TypeUtilities::MethodNameFromProperty($prop, $SetNotGet);
    }
}
