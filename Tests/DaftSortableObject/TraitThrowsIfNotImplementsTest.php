<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObjectRepository;

use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\NotSortableReadWrite;
use SignpostMarv\DaftObject\SortableReadWrite;
use SignpostMarv\DaftObject\Tests\TestCase;

class TraitThrowsIfNotImplementsTest extends TestCase
{
    public function test_CompareToDaftSortableObject() : void
    {
        $a = new NotSortableReadWrite();
        $b = new SortableReadWrite();

        static::expectException(ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            NotSortableReadWrite::class .
            ' does not implement ' .
            DaftSortableObject::class
        );

        $a->CompareToDaftSortableObject($b);
    }

    public function test_DaftSortableObjectProperties() : void
    {
        static::expectException(ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            NotSortableReadWrite::class .
            ' does not implement ' .
            DaftSortableObject::class
        );

        NotSortableReadWrite::DaftSortableObjectProperties();
    }
}
