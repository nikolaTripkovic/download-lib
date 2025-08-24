<?php

namespace CodingTask\Download;

use CodingTask\Download\Resolver\ProviderResolver;
use CodingTask\Download\Exceptions\DownloadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Main service class for downloading files from various cloud storage providers.
 * 
 * This class serves as the primary entry point for the download library.
 * It automatically detects the appropriate provider based on the URL and
 * handles the download process.
 */
class FileDownloader
{
    public function __construct(
        private readonly ProviderResolver $providerResolver
    ) {}

    public function download(string $url): UploadedFile
    {
        try {
            $provider = $this->providerResolver->resolve($url);
            return $provider->download($url);
        } catch (DownloadException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new DownloadException('Download failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function isSupported(string $url): bool
    {
        try {
            $this->providerResolver->resolve($url);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}