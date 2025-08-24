<?php

namespace CodingTask\Download\Providers;

use CodingTask\Download\Exceptions\DownloadException;

class DirectDownloader extends AbstractDownloader
{
    protected function prepareDownloadUrl(string $url): string
    {
        return $url;
    }

    protected function createDownloadException(\Throwable $e): DownloadException
    {
        return new DownloadException('Direct link download failed: ' . $e->getMessage(), 0, $e);
    }
}