<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Generator;
use SignpostMarv\DaftObject\NotPublicGetterPropertyException;
use SignpostMarv\DaftObject\NotPublicSetterPropertyException;
use SignpostMarv\DaftObject\PasswordHashTestObject;

class DaftObjectGetterSetterTest extends TestCase
{
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
        ];
    }

    public function dataProviderGetterBad() : iterable
    {
        $sources = $this->dataProviderGetterSetterGood();

        $generator = function () use ($sources) : Generator {
            foreach ($sources as $source) {
                if (false === $source[3]) {
                    yield [
                        $source[0],
                        $source[1],
                        $source[2],
                    ];
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
                    yield [
                        $source[0],
                        $source[1],
                        $source[2],
                    ];
                }
            }
        };

        return $generator();
    }

    /**
    * @dataProvider dataProviderGetterSetterGood
    */
    public function testGetterSetterGood(
        string $implementation,
        string $property,
        string $value,
        bool $publicGetter,
        bool $publicSetter,
        string $changedProperty = null
    ) : void {
        if (false === $publicGetter && false === $publicSetter) {
            $this->markTestSkipped(
                'Cannot run a test if property is neither a getter or setter'
            );
        } else {
            $arr = [];

            if ($publicGetter) {
                $arr[$property] = $value;
            }

            $obj = new $implementation($arr);

            if ($publicSetter) {
                $obj->$property = $value;
            }

            if ($publicGetter) {
                $this->assertSame($value, $obj->$property);
            } elseif ($publicSetter) {
                $this->assertTrue($obj->HasPropertyChanged($changedProperty));
            }
        }
    }

    /**
    * @dataProvider dataProviderGetterBad
    *
    * @depends testGetterSetterGood
    */
    public function testGetterBad(
        string $implementation,
        string $property,
        string $value
    ) : void {
        $obj = new $implementation([
            $property => $value,
        ]);

        $this->expectException(NotPublicGetterPropertyException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property not a public getter: %s::$%s',
                $implementation,
                $property
            )
        );

        $foo = $obj->$property;
    }

    /**
    * @dataProvider dataProviderSetterBad
    *
    * @depends testGetterSetterGood
    */
    public function testSetterBad(
        string $implementation,
        string $property,
        string $value
    ) : void {
        $obj = new $implementation();

        $this->expectException(NotPublicSetterPropertyException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property not a public setter: %s::$%s',
                $implementation,
                $property
            )
        );

        $obj->$property = $value;
    }
}
