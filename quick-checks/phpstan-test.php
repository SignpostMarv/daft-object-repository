<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\Tests\DefinitionAssistant\DefinesPropertyOnInterfaceClassImplementation;

$foo = new ReadWrite(['Foo' => 'bar']);

$foo->Foo = 'baz';

/**
* @var string
*/
$fooVal = $foo->Foo;

$foo->Foo = strrev($fooVal);

$bar = new DefinesPropertyOnInterfaceClassImplementation();

$shouldBeTrue = '' === $bar->foo;
$bar->foo = 'bar';
