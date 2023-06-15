<?php

namespace App\Libs;

class HtmlSanitizer
{
    public static function sanitizeArray(array $values, array $keys = null) : array
    {
        $keys = $keys ?: array_keys($values);

        foreach($keys as $key) {
            if(!array_key_exists($key, $values))
                continue;
            
            $values[$key] = self::sanitizeValue($values[$key]);
        }

        return $values;
    }

    public static function sanitizeValue($value)
    {
        $sanitizedValue = htmlentities($value);

        return $sanitizedValue;
    }
}