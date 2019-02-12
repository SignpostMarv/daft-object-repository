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
    * @template T as DaftJson
    *
    * @return array<int, DaftJson>|DaftJson
    *
    * @psalm-return array<int, T>|T
    */
    final public static function DaftJsonFromJsonType(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) {
        if ('[]' === mb_substr($jsonType, -2)) {
            /**
            * @psalm-var class-string<DaftObject>
            */
            $jsonType = mb_substr($jsonType, 0, -2);

            /**
            * @psalm-var class-string<T>
            */
            $jsonType = self::ThrowIfNotJsonType($jsonType);

            return self::DaftObjectFromJsonTypeArray($jsonType, $prop, $propVal, $writeAll);
        }

        /**
        * @psalm-var class-string<T>
        */
        $jsonType = $jsonType;

        return JsonTypeUtilities::ArrayToJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @param array<int|string, mixed> $array
    *
    * @psalm-param class-string<DaftJson> $type
    */
    public static function ThrowIfJsonDefNotValid(string $type, array $array) : array
    {
        $jsonProps = $type::DaftObjectJsonPropertyNames();
        $array = JsonTypeUtilities::FilterThrowIfJsonDefNotValid($type, $jsonProps, $array);
        $jsonDef = $type::DaftObjectJsonProperties();

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
    * @psalm-param class-string<DaftObject> $jsonType
    *
    * @psalm-return class-string<DaftJson>
    */
    private static function ThrowIfNotJsonType(string $jsonType) : string
    {
        if ( ! is_a($jsonType, DaftJson::class, DefinitionAssistant::IS_A_STRINGS)) {
            throw new ClassDoesNotImplementClassException($jsonType, DaftJson::class);
        }

        /**
        * @psalm-var class-string<DaftJson>
        */
        $jsonType = $jsonType;

        return $jsonType;
    }

    /**
    * @param array<string|int, string> $jsonDef
    */
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
                        $jsonDef[$prop],
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

    /**
    * @template T as DaftJson
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    private static function ArrayToJsonType(string $type, array $value, bool $writeAll) : DaftJson
    {
        return $type::DaftObjectFromJsonArray($value, $writeAll);
    }

    /**
    * @template T as DaftJson
    *
    * @param mixed[] $propVal
    *
    * @psalm-param class-string<T> $jsonType
    *
    * @return array<int, DaftJson>
    *
    * @psalm-return array<int, T>
    */
    private static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        return array_map(
            /**
            * @param mixed $val
            *
            * @psalm-return T
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
