<?php

namespace Wester\ChunkUpload\Language;

class Language
{
    /**
     * The language.
     * 
     * @var array
     */
    private static $language = [];

    /**
     * Set language.
     * 
     * @param  array  $language
     * @return void
     */
    public static function set(array $language)
    {
        self::$language = $language;
    }

    /**
     * Get an expression.
     * 
     * @param  string  $key
     * @return null|string
     */
    public static function get(string $key)
    {
        $keys = explode('.', $key);
        $value = self::$language;

        foreach ($keys as $key) {
            $value = $value[$key] ?? null;

            if (! is_array($value))
                return $value;
        }

        return null;
    }

    /**
     * Get an attribute.
     * 
     * @param  string  $attribute
     * @return string
     */
    public static function getAttribute(string $attribute)
    {
        return self::$language['attributes'][$attribute] ?? $attribute;
    }

    /**
     * Parse expression.
     * 
     * @param  string  $key
     * @param  array  $data
     */
    public static function expression(string $key, array $data)
    {
        $expression = self::get($key);
        
        return preg_replace_callback('/(:\S+)/', function ($match) use ($data) {
            if ($match[0] === ":attribute")
                return self::getAttribute($data['attribute']);

            return $data['value'];
        }, $expression);
    }
}
