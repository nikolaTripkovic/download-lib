<?php

namespace CodingTask\Download\Exceptions;

use Exception;

class FileException extends Exception
{
    public function __construct(string $message = "File error occurred", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
