<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use Error;
use InvalidArgumentException;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as ParentDefinitionAssistant;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DateTimeImmutableTestObject;
use SignpostMarv\DaftObject\DefinitionAssistant as BaseDefinitionAssistant;
use SignpostMarv\DaftObject\Tests\TestCase;
use TypeError;

class DefinitionAssistantTest extends TestCase
{
    public function testIsTypeUnregistered() : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(DaftObject::class));
        DefinitionAssistant::AutoRegisterType(DaftObject::class, 'foo');
        static::assertFalse(DefinitionAssistant::IsTypeUnregistered(DaftObject::class));
        static::assertCount(1, DefinitionAssistant::ObtainExpectedProperties(DaftObject::class));

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(
            DateTimeImmutableTestObject::class
        ));
        DefinitionAssistant::ClearTypes();
        DefinitionAssistant::RegisterAbstractDaftObjectType(DateTimeImmutableTestObject::class);
        static::assertCount(
            1,
            DefinitionAssistant::ObtainExpectedProperties(
                DateTimeImmutableTestObject::class
            )
        );
        DefinitionAssistant::ClearTypes();
    }

    public function DataProviderExceptionsInRegisterType() : array
    {
        return [
            [
                DaftObject::class,
                [],
                InvalidArgumentException::class,
                'Argument 4 must be specified!',
            ],
            [
                DaftObject::class,
                ['foo' => 'foo'],
                Error::class,
                'Cannot unpack array with string keys',
            ],
            [
                DaftObject::class,
                [1],
                TypeError::class,
                (
                    'Argument 2 passed to ' .
                    BaseDefinitionAssistant::class .
                    '::AutoRegisterType() must be of the type string, integer given, called in ' .
                    __FILE__ .
                    ' on line ' .
                    (__LINE__ + 24)
                ),
            ],
        ];
    }

    /**
    * @param array<int, string> $properties
    *
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

        DefinitionAssistant::AutoRegisterType($type, ...$properties);
    }

    public function testNotDaftObject() : void
    {
        DefinitionAssistant::ClearTypes();
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            'SignpostMarv\DaftObject\DefinitionAssistant::IsTypeUnregistered()' .
            ' must be an implementation of ' .
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
            ' must be an implementation of ' .
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

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(
            DefinesPropertyOnInterfaceClassImplementation::class
        ));

        static::assertNull(DefinitionAssistant::GetterMethodName(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        ));

        DefinitionAssistant::AutoRegisterType(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        );

        static::assertSame('GetFoo', DefinitionAssistant::GetterMethodName(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        ));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            ParentDefinitionAssistant::class .
            '::RegisterType()' .
            ' has already been registered!'
        );

        DefinitionAssistant::AutoRegisterType(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        );
    }
}
