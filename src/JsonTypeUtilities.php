<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;

class JsonTypeUtilities
{
    const IS_A_STRINGS = true;

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @psalm-return class-string<DaftJson>
    */
    public static function ThrowIfNotDaftJson(string $class) : string
    {
        if ( ! is_a($class, DaftJson::class, self::IS_A_STRINGS)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException($class);
        }

        return $class;
    }

    /**
    * @return array<int, DaftJson>|DaftJson
    */
    final public static function DaftJsonFromJsonType(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) {
        if ('[]' === mb_substr($jsonType, -2)) {
            $jsonType = self::ThrowIfNotJsonType(mb_substr($jsonType, 0, -2));

            return self::DaftObjectFromJsonTypeArray($jsonType, $prop, $propVal, $writeAll);
        }

        return JsonTypeUtilities::ArrayToJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @param array<int|string, mixed> $array
    *
    * @psalm-param class-string<DaftJson> $type
    */
    public static function ThrowIfJsonDefNotValid(string $type, array $array) : array
    {
        $jsonProps = TypeParanoia::EnsureArgumentIsArray($type::DaftObjectJsonPropertyNames());
        $array = JsonTypeUtilities::FilterThrowIfJsonDefNotValid($type, $jsonProps, $array);
        $jsonDef = TypeParanoia::EnsureArgumentIsArray($type::DaftObjectJsonProperties());

        $keys = array_keys($array);

        /**
        * @var array<int|string, mixed>
        */
        $out = array_combine($keys, array_map(
            JsonTypeUtilities::MakeMapperThrowIfJsonDefNotValid($type, $jsonDef, $array),
            $keys
        ));

        return $out;
    }

    /**
    * @psalm-return class-string<DaftJson>
    */
    private static function ThrowIfNotJsonType(string $jsonType) : string
    {
        if ( ! TypeParanoia::IsThingStrings($jsonType, DaftJson::class)) {
            throw new ClassDoesNotImplementClassException($jsonType, DaftJson::class);
        }

        /**
        * @psalm-var class-string<DaftJson>
        */
        $jsonType = $jsonType;

        return $jsonType;
    }

    private static function MakeMapperThrowIfJsonDefNotValid(
        string $class,
        array $jsonDef,
        array $array
    ) : Closure {
        $mapper =
            /**
            * @return mixed
            */
            function (string $prop) use ($jsonDef, $array, $class) {
                if (isset($jsonDef[$prop]) && false === is_array($array[$prop])) {
                    static::ThrowBecauseArrayJsonTypeNotValid(
                        $class,
                        TypeParanoia::EnsureArgumentIsString($jsonDef[$prop]),
                        $prop
                    );
                }

                return $array[$prop];
            };

        return $mapper;
    }

    /**
    * @psalm-param class-string<DaftJson> $class
    */
    private static function FilterThrowIfJsonDefNotValid(
        string $class,
        array $jsonProps,
        array $array
    ) : array {
        $filter = function (string $prop) use ($jsonProps, $array, $class) : bool {
            if ( ! in_array($prop, $jsonProps, DefinitionAssistant::IN_ARRAY_STRICT_MODE)) {
                throw new PropertyNotJsonDecodableException($class, $prop);
            }

            return false === is_null($array[$prop]);
        };

        return array_filter($array, $filter, ARRAY_FILTER_USE_KEY);
    }

    private static function ArrayToJsonType(string $type, array $value, bool $writeAll) : DaftJson
    {
        return self::ThrowIfNotJsonType($type)::DaftObjectFromJsonArray($value, $writeAll);
    }

    /**
    * @param mixed[] $propVal
    *
    * @psalm-param class-string<DaftJson> $jsonType
    *
    * @return array<int, DaftJson>
    */
    private static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        $jsonType = self::ThrowIfNotJsonType($jsonType);

        return array_map(
            /**
            * @param mixed $val
            */
            function ($val) use ($jsonType, $writeAll, $prop) : DaftJson {
                if (false === is_array($val)) {
                    throw new PropertyNotJsonDecodableShouldBeArrayException($jsonType, $prop);
                }

                return JsonTypeUtilities::ArrayToJsonType($jsonType, $val, $writeAll);
            },
            array_values($propVal)
        );
    }

    private static function ThrowBecauseArrayJsonTypeNotValid(
        string $class,
        string $type,
        string $prop
    ) : void {
        if ('[]' === mb_substr($type, -2)) {
            throw new PropertyNotJsonDecodableShouldBeArrayException($class, $prop);
        }
        throw new PropertyNotJsonDecodableShouldBeArrayException($type, $prop);
    }
}
