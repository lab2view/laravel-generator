<?php

namespace Lab2view\Generator\Exceptions;

use Exception;

class StubException extends Exception
{
    public static function fileNotFound(string $file): self
    {
        return new StubException('Stub file does not exists: '.$file);
    }
}
