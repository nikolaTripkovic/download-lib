<?php

namespace CodingTask\Download\Providers;

use CodingTask\Download\DownloaderInterface;
use CodingTask\Download\Exceptions\DownloadException;
use CodingTask\Download\Util\FileUtils;
use CodingTask\Download\Util\StreamDownloader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractDownloader implements DownloaderInterface
{
    private const MAX_DIRECT_DOWNLOAD_SIZE = 0.1 * 1024 * 1024;

    protected readonly StreamDownloader $streamDownloader;
    
    public function __construct(
        protected readonly HttpClientInterface $httpClient
    ) {
        $this->streamDownloader = new StreamDownloader($httpClient);
    }

    public function download(string $url): UploadedFile
    {
        $file = null;
        try {
            $downloadUrl = $this->prepareDownloadUrl($url);
            $response = $this->httpClient->request('GET', $downloadUrl, [
                'max_redirects' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Connection' => 'keep-alive',
                ],
            ]);

            $headers = $response->getHeaders();
            if($this->isHtmlResponse($headers)) {
                $this->handleHtmlResponse($response);
            }

            $fileInfo = FileUtils::resolveFilenameAndMime($downloadUrl, $headers);
            if ($fileInfo === null) {
                throw new \RuntimeException('Unable to determine file information from response.');
            }
            $folder =  __DIR__ . '/../../downloads'; // for testing purpose
            $file = FileUtils::createFile($fileInfo->fileName, $folder);

            // Determine download method based on file size
            if ($this->shouldUseDirectDownload($headers)) {
                $this->downloadFileDirectly($response, $file);
            } else {
                $this->downloadWithStreaming($downloadUrl, $file);
            }
            
            return new UploadedFile(
                $file,
                $fileInfo->fileName,
                $fileInfo->contentType,
                null,
                true
            );
        } catch (\Throwable $e) {
            FileUtils::cleanupFile($file);
            throw $this->createDownloadException($e);
        }
    }

    /**
     * Check if response headers indicate HTML content
     */
    protected function isHtmlResponse(array $headers): bool
    {
        $contentType = $headers['content-type'][0] ?? '';
        return stripos($contentType, 'text/html') !== false || 
               stripos($contentType, 'application/xhtml') !== false;
    }

    /**
     * Determine if we should use direct download based on file size
     */
    private function shouldUseDirectDownload(array $headers): bool
    {
        $contentLength = $headers['content-length'][0] ?? null;
        if ($contentLength === null) {
            return false;
        }
        $fileSize = (int) $contentLength;
        return $fileSize <= self::MAX_DIRECT_DOWNLOAD_SIZE;
    }

    /**
     * Download file directly using getContent() for small files
     */
    private function downloadFileDirectly(ResponseInterface $response, string $filePath): void
    {
        try {
            $content = $response->getContent();

            if (file_put_contents($filePath, $content) === false) {
                throw new \RuntimeException('Failed to write file content.');
            }
        } catch (\Throwable $e) {
            throw $this->createDownloadException($e);
        }
    }

    /**
     * Download file using streaming for large files
     */
    private function downloadWithStreaming(string $downloadUrl, string $filePath): void
    {
        $fileHandle = fopen($filePath, 'wb');

        if ($fileHandle === false) {
            throw $this->createDownloadException(new \RuntimeException('Failed to create file.'));
        }
        try {
            $this->streamDownloader->downloadToFileHandle($downloadUrl, $fileHandle);
        } catch (\Throwable $e) {
            throw $this->createDownloadException($e);
        } finally {
            fclose($fileHandle);
        }
    }

    /**
     * Handle HTML response. Override in subclasses for specific logic.
     * Default implementation throws exception
     */
    protected function handleHtmlResponse(ResponseInterface $response): void
    {
        throw new DownloadException('Unable to process HTML file');
    }

    /**
     * Prepare the download URL. Each provider implements this differently.
     */
    abstract protected function prepareDownloadUrl(string $url): string;

    /**
     * Create appropriate exception for this provider.
     */
    abstract protected function createDownloadException(\Throwable $e): DownloadException;
}
