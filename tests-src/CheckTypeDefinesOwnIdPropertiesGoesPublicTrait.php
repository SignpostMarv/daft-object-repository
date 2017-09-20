<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

trait CheckTypeDefinesOwnIdPropertiesGoesPublic
{
    public static function CheckTypeDefinesOwnIdPropertiesGoesPublic(
        DaftObject $object
    ) : bool {
        return static::CheckTypeDefinesOwnIdProperties($object);
    }
}
