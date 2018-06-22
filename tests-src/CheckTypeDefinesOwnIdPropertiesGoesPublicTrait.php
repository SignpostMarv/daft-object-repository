<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\TypeUtilities;

trait CheckTypeDefinesOwnIdPropertiesGoesPublic
{
    public static function CheckTypeDefinesOwnIdPropertiesGoesPublic(
        DaftObject $object
    ) : bool {
        return TypeUtilities::CheckTypeDefinesOwnIdProperties($object::class);
    }
}
