<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;

class JsonTypeUtilities
{
    public static function ThrowIfNotDaftJson(string $class) : void
    {
        if (false === is_a($class, DaftJson::class, true)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException($class);
        }
    }

    public static function ThrowIfNotJsonType(string $jsonType) : void
    {
        if (false === is_a($jsonType, DaftJson::class, true)) {
            throw new ClassDoesNotImplementClassException($jsonType, DaftJson::class);
        }
    }

    public static function MakeMapperThrowIfJsonDefNotValid(
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
                        (string) $jsonDef[$prop],
                        $prop
                    );
                }

                return $array[$prop];
            };

        return $mapper;
    }

    public static function FilterThrowIfJsonDefNotValid(
        string $class,
        array $jsonProps,
        array $array
    ) : array {
        $filter = function (string $prop) use ($jsonProps, $array, $class) : bool {
            if (false === in_array($prop, $jsonProps, true)) {
                throw new PropertyNotJsonDecodableException($class, $prop);
            }

            return false === is_null($array[$prop]);
        };

        return array_filter($array, $filter, ARRAY_FILTER_USE_KEY);
    }

    public static function ArrayToJsonType(string $type, array $value, bool $writeAll) : DaftJson
    {
        self::ThrowIfNotJsonType($type);

        /**
        * @var DaftJson $type
        */
        $type = $type;

        return $type::DaftObjectFromJsonArray($value, $writeAll);
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
            $jsonType = mb_substr($jsonType, 0, -2);

            self::ThrowIfNotJsonType($jsonType);

            return self::DaftObjectFromJsonTypeArray($jsonType, $prop, $propVal, $writeAll);
        }

        return JsonTypeUtilities::ArrayToJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @param array<int|string, mixed> $array
    */
    public static function ThrowIfJsonDefNotValid(string $type, array $array) : array
    {
        self::ThrowIfNotDaftJson($type);
        $jsonProps = (array) $type::DaftObjectJsonPropertyNames();
        $array = JsonTypeUtilities::FilterThrowIfJsonDefNotValid($type, $jsonProps, $array);
        $jsonDef = (array) $type::DaftObjectJsonProperties();

        $keys = array_keys($array);

        return array_combine($keys, array_map(
            JsonTypeUtilities::MakeMapperThrowIfJsonDefNotValid($type, $jsonDef, $array),
            $keys
        ));
    }

    /**
    * @param mixed[] $propVal
    *
    * @return array<int, DaftJson>
    */
    protected static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        JsonTypeUtilities::ThrowIfNotJsonType($jsonType);

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
