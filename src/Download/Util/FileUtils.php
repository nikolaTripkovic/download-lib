<?php

namespace CodingTask\Download\Util;

use CodingTask\Download\Exceptions\FileException;
use CodingTask\Download\ValueObject\FileInfo;

/**
 * Utility class for handling file operations and metadata extraction.
 */
class FileUtils
{
    public static function resolveFilenameAndMime(string $url, array $headers): FileInfo
    {
        $headers = array_change_key_case($headers, CASE_LOWER);
        $contentType = $headers['content-type'][0] ?? 'application/octet-stream';
        $contentDisposition = $headers['content-disposition'][0] ?? '';

        $fileName = self::extractFilenameFromDisposition($contentDisposition)
            ?? self::extractFilenameFromUrl($url);
        $fileName = self::sanitizeFileName($fileName);

        $fileName = self::ensureFileExtension($fileName, $contentType);
        return new FileInfo(
            fileName: $fileName,
            contentType: $contentType
        );
    }
    private static function extractFilenameFromDisposition(string $disposition): ?string
    {
        $patterns = [
            '/filename="([^"]+)"/',
            '/filename=([^;]+)/',
            '/filename\*=UTF-8\'\'([^;]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $disposition, $matches)) {
                $filename = urldecode($matches[1]);
                return basename($filename);
            }
        }
        return null;
    }

    private static function extractFilenameFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $filename = basename($path);
        // Remove query parameters from filename
        if (str_contains($filename, '?')) {
            $filename = strtok($filename, '?');
        }
        return $filename ?: null;
    }

    public static function createFile(string $fileName, string $folder): string
    {
        if (empty($fileName)) {
            throw new \InvalidArgumentException('File name cannot be empty');
        }
        if (!is_dir($folder)) {
            if (!mkdir($folder, 0775, true) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Unable to create folder: %s', $folder));
            }
        }
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $counter = 1;
        $finalFileName = $fileName;
        $maxAttempts = 1000;

        // Check if file exists and create unique name
        while (file_exists($folder . DIRECTORY_SEPARATOR . $finalFileName) && $counter <= $maxAttempts) {
            $finalFileName = $baseName . '_' . $counter . ($extension ? '.' . $extension : '');
            $counter++;
        }
        if ($counter > $maxAttempts) {
            throw new FileException('Unable to generate unique file name after maximum attempts');
        }
        return $folder . DIRECTORY_SEPARATOR . $finalFileName;
    }

    public static function cleanupFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            unlink($path);
        }
        if ($path && file_exists($path) && !unlink($path)) {
            throw new FileException("Failed to delete file: {$path}");
        }
    }

    private static function sanitizeFileName(?string $fileName): string
    {
        $fileName = $fileName ?: 'downloaded_file';
        $fileName = urldecode($fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);

        return $fileName;
    }

    private static function ensureFileExtension(string $fileName, string $contentType): string
    {
        if (pathinfo($fileName, PATHINFO_EXTENSION)) {
            return $fileName;
        }

        $extension = self::getExtensionFromMimeType($contentType);
        if ($extension) {
            return $fileName . '.' . $extension;
        }
        return $fileName;
    }

    private static function getExtensionFromMimeType(string $mimeType): ?string
    {
        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/json' => 'json',
            'text/plain' => 'txt',
            'text/html' => 'html',
            'text/css' => 'css',
            'text/javascript' => 'js',
            'application/javascript' => 'js',
            'application/xml' => 'xml',
            'application/zip' => 'zip',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-word' => 'doc',
            'application/vnd.ms-powerpoint' => 'ppt',
        ];

        return $mimeToExtension[$mimeType] ?? null;
    }

}
