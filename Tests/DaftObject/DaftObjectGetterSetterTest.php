<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTimeImmutable;
use Generator;
use RuntimeException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\MultiTypedArrayPropertiesTester;
use SignpostMarv\DaftObject\NotPublicGetterPropertyException;
use SignpostMarv\DaftObject\NotPublicSetterPropertyException;
use SignpostMarv\DaftObject\PasswordHashTestObject;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\Tests\TestCase;

class DaftObjectGetterSetterTest extends TestCase
{
    /**
    * @return array<int, array<int, mixed>>
    */
    public function dataProviderGetterSetterGood() : array
    {
        return [
            [
                PasswordHashTestObject::class,
                'password',
                'asdf',
                false,
                true,
                'passwordHash',
            ],
            [
                PasswordHashTestObject::class,
                'passwordHash',
                password_hash('asdf', PASSWORD_DEFAULT),
                true,
                false,
            ],
            [
                ReadWrite::class,
                'Foo',
                'bar',
                true,
                true,
                'Foo',
            ],
            [
                MultiTypedArrayPropertiesTester::class,
                'dates',
                [
                    new DateTimeImmutable(),
                    new DateTimeImmutable(),
                ],
                true,
                true,
                'dates',
            ],
            [
                MultiTypedArrayPropertiesTester::class,
                'datesOrStrings',
                [
                    new DateTimeImmutable(),
                    'foo',
                    new DateTimeImmutable(),
                    'bar',
                    ' baz ',
                ],
                true,
                true,
                'datesOrStrings',
            ],
        ];
    }

    public function dataProviderGetterSetterGoodGetterOnly() : Generator
    {
        foreach ($this->dataProviderGetterSetterGood() as $args) {
            list($implementation, $property, $value, $publicGetter, $publicSetter) = $args;

            if ( ! is_bool($publicGetter) || ! is_bool($publicSetter)) {
                throw new RuntimeException('Getter & Setter flags must be boolean!');
            }

            /**
            * @var string|null
            */
            $changedProperty = $args[5] ?? null;

            if ($publicGetter && ! $publicSetter) {
                yield [$implementation, $property, $value, $changedProperty];
            }
        }
    }

    public function dataProviderGetterSetterGoodSetterOnly() : Generator
    {
        foreach ($this->dataProviderGetterSetterGood() as $args) {
            list($implementation, $property, $value, $publicGetter, $publicSetter) = $args;

            if ( ! is_bool($publicGetter) || ! is_bool($publicSetter)) {
                throw new RuntimeException('Getter & Setter flags must be boolean!');
            }

            /**
            * @var string|null
            */
            $changedProperty = $args[5] ?? null;

            if ( ! $publicGetter && $publicSetter && is_string($changedProperty)) {
                yield [$implementation, $property, $value, $changedProperty];
            }
        }
    }

    public function dataProviderGetterSetterGoodGetterSetter() : Generator
    {
        foreach ($this->dataProviderGetterSetterGood() as $args) {
            list($implementation, $property, $value, $publicGetter, $publicSetter) = $args;

            if ( ! is_bool($publicGetter) || ! is_bool($publicSetter)) {
                throw new RuntimeException('Getter & Setter flags must be boolean!');
            }

            /**
            * @var string|null
            */
            $changedProperty = $args[5] ?? null;

            if ($publicGetter && $publicSetter && is_string($changedProperty)) {
                yield [$implementation, $property, $value, $changedProperty];
            }
        }
    }

    public function dataProviderGetterBad() : iterable
    {
        $sources = $this->dataProviderGetterSetterGood();

        $generator = function () use ($sources) : Generator {
            foreach ($sources as $source) {
                if (false === $source[3]) {
                    yield [$source[0], $source[1], $source[2]];
                }
            }
        };

        return $generator();
    }

    public function dataProviderSetterBad() : iterable
    {
        $sources = $this->dataProviderGetterSetterGood();

        $generator = function () use ($sources) : Generator {
            foreach ($sources as $source) {
                if (false === $source[4]) {
                    yield [$source[0], $source[1], $source[2]];
                }
            }
        };

        return $generator();
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObjectCreatedByArray> $implementation
    *
    * @dataProvider dataProviderGetterSetterGoodGetterOnly
    */
    public function testGetterOnly(
        string $implementation,
        string $property,
        $value,
        string $changedProperty = null
    ) : void {
        $arr = [];

        $arr[$property] = $value;

        $obj = new $implementation($arr);

        static::assertSame($value, $obj->__get($property));
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObjectCreatedByArray> $implementation
    *
    * @dataProvider dataProviderGetterSetterGoodSetterOnly
    */
    public function testSetterOnly(
        string $implementation,
        string $property,
        $value,
        string $changedProperty
    ) : void {
        $arr = [];

        $obj = new $implementation($arr);

        $obj->__set($property, $value);

        static::assertTrue($obj->HasPropertyChanged($changedProperty));
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $implementation
    *
    * @dataProvider dataProviderGetterSetterGoodGetterSetter
    */
    public function testGetterSetterGood(
        string $implementation,
        string $property,
        $value,
        string $changedProperty
    ) : void {
        $obj = new $implementation([]);

        $obj->__set($property, $value);

        static::assertSame($value, $obj->__get($property));
        static::assertTrue($obj->HasPropertyChanged($changedProperty));
    }

    /**
    * @param mixed $value
    *
    * @psalm-param class-string<DaftObject> $implementation
    *
    * @dataProvider dataProviderGetterBad
    *
    * @depends testGetterSetterGood
    */
    public function testGetterBad(string $implementation, string $property, $value) : void
    {
        $obj = new $implementation([
            $property => $value,
        ]);

        $this->expectException(NotPublicGetterPropertyException::class);
        $this->expectExceptionMessage(sprintf(
            'Property not a public getter: %s::$%s',
            $implementation,
            $property
        ));

        $obj->__get($property);
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $implementation
    *
    * @dataProvider dataProviderSetterBad
    *
    * @depends testGetterSetterGood
    */
    public function testSetterBad(string $implementation, string $property, $value) : void
    {
        $obj = new $implementation();

        $this->expectException(NotPublicSetterPropertyException::class);
        $this->expectExceptionMessage(sprintf(
            'Property not a public setter: %s::$%s',
            $implementation,
            $property
        ));

        $obj->__set($property, $value);
    }
}
