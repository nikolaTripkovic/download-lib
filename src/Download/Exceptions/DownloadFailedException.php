<?php

namespace CodingTask\Download\Exceptions;

/**
 * Exception thrown when a download operation fails.
 */
class DownloadFailedException extends DownloadException
{
    public function __construct(string $message = 'Download failed', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

