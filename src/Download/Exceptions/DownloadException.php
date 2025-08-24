<?php

namespace CodingTask\Download\Exceptions;

/**
 * Base exception class for download-related errors.
 */
class DownloadException extends \Exception
{
    public function __construct(string $message = 'Download error occurred', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}



