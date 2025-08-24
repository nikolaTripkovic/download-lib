<?php

namespace CodingTask\Download\Exceptions;

class InvalidUrlException extends DownloadException
{
    public function __construct(string $message = 'Invalid URL provided', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

