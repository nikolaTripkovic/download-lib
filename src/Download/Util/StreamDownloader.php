<?php

namespace CodingTask\Download\Util;

use CodingTask\Download\Exceptions\DownloadException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function downloadToFileHandle(string $downloadUrl, $fileHandle): void
    {
        try {
            $response = $this->httpClient->request('GET', $downloadUrl);

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
