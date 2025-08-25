<?php

namespace CodingTask\Download\Util;

use CodingTask\Download\Exceptions\DownloadException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Utility class for streaming downloads
 */
class StreamDownloader
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {}

    /**
     * Downloads a file to an existing file handle and returns response headers
     *
     */
    public function downloadToFileHandle(ResponseInterface $response, $fileHandle): void
    {
        try {
            foreach ($this->httpClient->stream($response) as $chunk) {
                if ($chunk->isTimeout() || $chunk->isLast()) {
                    break;
                }
                fwrite($fileHandle, $chunk->getContent());
            }
        } catch (\Throwable $e) {
            throw new DownloadException('Failed to download file: ' . $e->getMessage(), 0, $e);
        }
    }

}
