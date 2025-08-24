<?php

namespace CodingTask\Download\Resolver;

use CodingTask\Download\DownloaderInterface;
use CodingTask\Download\Exceptions\InvalidUrlException;
use CodingTask\Download\Services\OneDriveDownloadService;
use CodingTask\Download\ValueObject\ParsedUrl;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use CodingTask\Download\Providers\GoogleDriveDownloader;
use CodingTask\Download\Providers\OneDriveDownloader;
use CodingTask\Download\Providers\DirectDownloader;

class ProviderResolver
{
    private OneDriveDownloadService $oneDriveDownloadService;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->oneDriveDownloadService = new OneDriveDownloadService($this->httpClient);
    }

    public function resolve(string $url): DownloaderInterface
    {
        if (empty($url)) {
            throw new InvalidUrlException('URL is empty');
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException('URL is not valid');
        }

        $parsedUrl = ParsedUrl::fromString($url);
        $host = $parsedUrl->host;
        $path = $parsedUrl->path;
        $query = $parsedUrl->query;

        // Google Drive detection
        if (str_contains($host, 'google.com') && str_contains($path, '/file/d/')) {
            return new GoogleDriveDownloader($this->httpClient);
        }
        // OneDrive detection
        if (str_contains($host, '1drv.ms') || 
            (str_contains($host, 'onedrive.live.com')) && str_contains($query, 'redeem=')) {
            return new OneDriveDownloader($this->httpClient, $this->oneDriveDownloadService);
        }
        // Default to direct downloader for other URLs
        return new DirectDownloader($this->httpClient);
    }
}