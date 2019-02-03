<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class TypeCertainty
{
    const INDEX_FIRST_ARG = 1;

    const BOOL_VAR_EXPORT_RETURN = true;

    /**
    * @param mixed $maybe
    */
    public static function VarExportNonScalars($maybe) : string
    {
        if (is_string($maybe)) {
            return $maybe;
        }

        return
            is_scalar($maybe)
                ? (string) $maybe
                : var_export($maybe, self::BOOL_VAR_EXPORT_RETURN);
    }
}
