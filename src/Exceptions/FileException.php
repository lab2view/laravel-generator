<?php

namespace Lab2view\Generator\Exceptions;

use Exception;

class FileException extends Exception
{
    public static function notWritableDirectory(string $directory): self
    {
        return new FileException('Not writable directory, check permissions: '.$directory);
    }
}
