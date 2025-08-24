<?php

namespace CodingTask\Download\Services;

use CodingTask\Download\Exceptions\DownloadFailedException;
use CodingTask\Download\Exceptions\InvalidUrlException;
use CodingTask\Download\ValueObject\OneDriveFileInfo;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OneDriveDownloadService
{
    private const API_BADGER_URL = 'https://api-badgerp.svc.ms/v1.0/token';
    private const BODY_APP_ID = '5cbed6ac-a083-4e14-b191-b4ba07653de2';
    private const HEADER_APP_ID = '1141147648';
    private const MICROSOFT_API_URL = 'https://my.microsoftpersonalcontent.com/_api/v2.0/shares/u!%s/driveitem';
    private const QUERY = 'query';
    const REDEEM = 'redeem';
    const TOKEN = 'token';
    const DOWNLOAD_URL_FIELD = '@content.downloadUrl';
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }

    /**
     * Provides access token from Microsoft Identity platform
     */
    private function getBadgerToken(): ?string
    {
        $postData = [
            'AppId' => self::BODY_APP_ID
        ];
        $headerData = [
            'Content-Type' => 'application/json',
            'AppId' => self::HEADER_APP_ID
        ];

        try {
            $response = $this->httpClient->request('POST', self::API_BADGER_URL, [
                'headers' => $headerData,
                'json' => $postData
            ]);

            $data = $response->toArray();
            return $data[self::TOKEN] ?? null;

        } catch (TransportExceptionInterface |
                ClientExceptionInterface |
                ServerExceptionInterface |
                RedirectionExceptionInterface |
                DecodingExceptionInterface $e) {
            return null;
        }
    }

    public function getDownloadUrl(string $url): ?string
    {
        $redeem = self::extractRedeemFromUrl($url);
        $badgerAuthToken = $this->getBadgerToken();
        if (!$badgerAuthToken) {
            return null;
        }

        $url = sprintf(self::MICROSOFT_API_URL, $redeem);
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    "Authorization" => "Badger {$badgerAuthToken}",
                    "Prefer" => "autoredeem",
                ],
            ]);

            $data = $response->toArray();
            return $data[self::DOWNLOAD_URL_FIELD] ?? null;
        } catch (TransportExceptionInterface |
                ClientExceptionInterface |
                ServerExceptionInterface |
                RedirectionExceptionInterface |
                DecodingExceptionInterface $e) {
            throw new DownloadFailedException('Failed to fetch OneDrive download URL.', 0, $e);
        }
    }

    private function extractRedeemFromUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!isset($parts[self::QUERY])) {
            throw new InvalidUrlException('Wrong url format. Missing query part.');
        }
        parse_str($parts[self::QUERY], $queryParams);

        if (!isset($queryParams[self::REDEEM])) {
            throw new InvalidUrlException('URL does not contain a redeem parameter.');
        }
        return $queryParams[self::REDEEM];
    }

}
