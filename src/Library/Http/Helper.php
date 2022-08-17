<?php

namespace App\Library\Http;

class Helper
{
    public static function getRootDir(string $path = null): string
    {
        return dirname(__DIR__, 3) . ($path ? '/' . $path : '');
    }
}
