<?php

namespace App\Library;

class Helper
{
    public static function getRootDir(string $path = null): string
    {
        return dirname(__DIR__, 2) . ($path ? '/' . $path : '');
    }
}
