<?php

namespace CodingTask\Download\Providers;

use CodingTask\Download\Exceptions\DownloadException;
use CodingTask\Download\Exceptions\DownloadFailedException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GoogleDriveDownloader extends AbstractDownloader
{
    private const GOOGLE_DOWNLOAD_URL = 'https://drive.google.com/uc?export=download&id=%s';
    private const HTML_TITLE_PATTERN = '~<html.*<title>(.*?)</title>~si';
    private const FILE_ID_PATTERN = '/(?:\/file\/d\/|id=)([a-zA-Z0-9_-]+)/';
    private const SIGN_IN_TITLE = 'Sign-in';
    private const VIRUS_SCAN_WARNING_TITLE = 'Virus scan warning';

    protected function prepareDownloadUrl(string $url): string
    {
        $fileId = $this->extractFileIdFromUrl($url);
        return sprintf(self::GOOGLE_DOWNLOAD_URL, $fileId);
    }

    protected function createDownloadException(\Throwable $e): DownloadException
    {
        return new DownloadFailedException('Failed download from Google Drive: ' . $e->getMessage(), 0, $e);
    }

    /**
     * Handle HTML response from Google Drive with specific error messages
     */
    protected function handleHtmlResponse(ResponseInterface $response): void
    {
        try {
            if (preg_match(self::HTML_TITLE_PATTERN, $response->getContent(), $matches)) {
                $title = $matches[1];
                if (str_contains($title, self::SIGN_IN_TITLE)) {
                    throw new \RuntimeException('Google Drive file is not publicly accessible.');

                }
                if (str_contains($title, self::VIRUS_SCAN_WARNING_TITLE)) {
                    // TODO: This is possible to solve using hidden fields in form
                    throw new \RuntimeException('File is too large for Google to scan for viruses.');
                }
                throw new \RuntimeException('Received HTML instead of file.');
            }
        } catch (\Throwable $e) {
            throw $this->createDownloadException($e);
        }
    }

    private function extractFileIdFromUrl(string $url): string
    {
        if (preg_match(self::FILE_ID_PATTERN, $url, $matches)) {
            return $matches[1];
        }

        throw $this->createDownloadException(
            new \RuntimeException('Invalid Google Drive URL.')
        );
    }
}