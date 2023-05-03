<?php

namespace Lab2view\Generator\Exceptions;

use Exception;

class StubException extends Exception
{
    public static function fileNotFound($file): static
    {
        return new static('Stub file does not exists: ' . $file);
    }
}
