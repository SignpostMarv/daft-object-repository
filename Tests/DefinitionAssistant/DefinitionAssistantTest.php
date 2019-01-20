<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use InvalidArgumentException;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\Tests\TestCase;

class DefinitionAssistantTest extends TestCase
{
    public function testIsTypeUnregistered() : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(DaftObject::class));
        DefinitionAssistant::RegisterType(DaftObject::class, ['foo']);
        static::assertFalse(DefinitionAssistant::IsTypeUnregistered(DaftObject::class));
        static::assertCount(1, DefinitionAssistant::ObtainExpectedProperties(DaftObject::class));

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(
            AbstractArrayBackedDaftObject::class
        ));
        DefinitionAssistant::RegisterAbstractDaftObjectType(AbstractArrayBackedDaftObject::class);
        static::assertCount(
            1,
            DefinitionAssistant::ObtainExpectedProperties(
                AbstractArrayBackedDaftObject::class
            )
        );
        DefinitionAssistant::ClearTypes();
    }

    public function testAlreadyRegistered() : void
    {
        DefinitionAssistant::ClearTypes();
        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(DaftObject::class));
        DefinitionAssistant::RegisterType(DaftObject::class, ['foo']);
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::RegisterType()' .
            ' has already been registered!'
        );

        DefinitionAssistant::RegisterType(DaftObject::class, ['foo']);
    }

    public function DataProviderExceptionsInRegisterType() : array
    {
        return [
            [
                DaftObject::class,
                [],
                InvalidArgumentException::class,
                (
                    'Argument 2 passed to ' .
                    'SignpostMarv\DaftObject\DefinitionAssistant::RegisterType()' .
                    ' must be a non-empty array!'
                ),
            ],
            [
                DaftObject::class,
                ['foo' => 'foo'],
                InvalidArgumentException::class,
                (
                    'Argument 2 passed to ' .
                    'SignpostMarv\DaftObject\DefinitionAssistant::RegisterType()' .
                    ' must be an array with only integer keys!'
                ),
            ],
            [
                DaftObject::class,
                [1],
                InvalidArgumentException::class,
                (
                    'Argument 2 passed to ' .
                    'SignpostMarv\DaftObject\DefinitionAssistant::RegisterType()' .
                    ' must be an array of shape array<int, string>!'
                ),
            ],
        ];
    }

    /**
    * @dataProvider DataProviderExceptionsInRegisterType
    */
    public function testExceptionsInRegisterType(
        string $type,
        array $properties,
        string $exception,
        string $message
    ) : void {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered($type));
        static::expectException($exception);
        static::expectExceptionMessage($message);

        DefinitionAssistant::RegisterType($type, $properties);
    }

    public function testNotStringOrObject() : void
    {
        DefinitionAssistant::ClearTypes();
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::ObtainExpectedProperties()' .
            ' must be either a string or an object, integer given!'
        );

        DefinitionAssistant::ObtainExpectedProperties(1);
    }

    public function testNotDaftObject() : void
    {
        DefinitionAssistant::ClearTypes();
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::ObtainExpectedProperties()' .
            ' must be either a string or an instance of ' .
            DaftObject::class .
            ', ' .
            get_class($this) .
            ' given!'
        );

        DefinitionAssistant::ObtainExpectedProperties($this);
    }

    public function testNotDaftObjectIsTypeUnregistered() : void
    {
        DefinitionAssistant::ClearTypes();
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::IsTypeUnregistered()' .
            ' must be an instance of ' .
            DaftObject::class .
            ', ' .
            get_class($this) .
            ' given!'
        );

        DefinitionAssistant::IsTypeUnregistered(static::class);
    }

    public function testRegisterAbstractDaftObjectTypeIsNotAbstractDaftObject() : void
    {
        DefinitionAssistant::ClearTypes();
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::RegisterAbstractDaftObjectType()' .
            ' must be an implementation of ' .
            AbstractDaftObject::class .
            ', ' .
            DaftObject::class .
            ' given!'
        );

        DefinitionAssistant::RegisterAbstractDaftObjectType(DaftObject::class);
    }

    public function testRegisterAbstractDaftObjectTypeHasAlreadyBeenRegistered() : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(AbstractDaftObject::class));

        DefinitionAssistant::RegisterAbstractDaftObjectType(AbstractDaftObject::class);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::RegisterAbstractDaftObjectType()' .
            ' has already been registered!'
        );

        DefinitionAssistant::RegisterAbstractDaftObjectType(AbstractDaftObject::class);
    }
}
