<?php

namespace Wester\ChunkUpload;

class StringHelper
{
    /**
     * Convert camel to kebab.
     * 
     * @param  string  $string
     * @return string
     */
    public static function camelToKebab(string $string)
    {
        if (preg_match ('/[A-Z]/', $string) === 0)
            return $string;

        return strtolower(
            preg_replace_callback('/([a-z])([A-Z])/', function ($match) {
                return $match[1] . "-" . strtolower ($match[2]); 
            }, $string)
        );
    }
}
