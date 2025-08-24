<?php

namespace CodingTask\Download\Providers;

use CodingTask\Download\Exceptions\DownloadException;
use CodingTask\Download\Exceptions\DownloadFailedException;
use CodingTask\Download\Services\OneDriveDownloadService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class OneDriveDownloader extends AbstractDownloader
{
    const ONE_DRIVE_SHORT_URL_HOST = '1drv.ms';
    private OneDriveDownloadService $downloadService;

    public function __construct(
        HttpClientInterface $httpClient,
        OneDriveDownloadService $downloadService
    ) {
        parent::__construct($httpClient);
        $this->downloadService = $downloadService;
    }
    protected function prepareDownloadUrl(string $url): string
    {
        try {
            $url = $this->resolveShortUrl($url);
            $downloadUrl = $this->downloadService->getDownloadUrl($url);
            if ($downloadUrl === null) {
                throw new \RuntimeException('Failed to generate download URL from OneDrive.');
            }
            return $downloadUrl;
        } catch (\Throwable $e) {
            throw $this->createDownloadException($e);
        }
    }

    private function resolveShortUrl(string $url): ?string
    {
        if(!self::isShortUrl($url)) {
            return $url;
        }
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Connection' => 'keep-alive',
                ],
            ]);

            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 400) {
                throw new \RuntimeException("Failed to resolve short URL, HTTP status $status");
            }

            return $response->getInfo('url') ?? throw new \RuntimeException('Failed to resolve short URL');
        } catch (\Throwable $e) {
            throw $this->createDownloadException($e);
        }
    }

    protected function createDownloadException(\Throwable $e): DownloadException
    {
        return new DownloadFailedException('Failed download from OneDrive: ' . $e->getMessage(), 0, $e);
    }

    private function isShortUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host === self::ONE_DRIVE_SHORT_URL_HOST;
    }

}
