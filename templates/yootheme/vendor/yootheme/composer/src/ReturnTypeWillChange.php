<?php

namespace YOOtheme\Composer;

class ReturnTypeWillChange
{
    /**
     * Add PHP attribute, if return type changes.
     */
    public static function replace($file, $code)
    {
        $subst = '$1#[\\\\ReturnTypeWillChange]$1$2';

        if (strpos($code, 'function jsonSerialize')) {
            $code = preg_replace('/(\s*)((public\s+)?function jsonSerialize\()/', $subst, $code);
        }

        if (strpos($code, 'function getIterator')) {
            $code = preg_replace('/(\s*)((public\s+)?function getIterator\()/', $subst, $code);
        }

        if (strpos($code, 'function offsetGet')) {
            $code = preg_replace('/(\s*)((public\s+)?function offset(Get|Set|Unset|Exists)\()/', $subst, $code);
        }

        if (strpos($code, 'function count')) {
            $code = preg_replace('/(\s*)((public\s+)?function count\()/', $subst, $code);
        }

        return $code;
    }
}
