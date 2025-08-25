<?php

require_once 'vendor/autoload.php';

use CodingTask\Download\FileDownloader;
use CodingTask\Download\Resolver\ProviderResolver;
use CodingTask\Download\Exceptions\DownloadException;
use CodingTask\Download\Exceptions\DownloadFailedException;
use CodingTask\Download\Exceptions\InvalidUrlException;
use CodingTask\Download\Services\OneDriveDownloadService;
use Symfony\Component\HttpClient\HttpClient;

// Create HTTP client with reasonable defaults
$httpClient = HttpClient::create([
    'timeout' => 30,
    'max_redirects' => 5,
    'headers' => [
        'User-Agent' => 'DownloadLib/1.0'
    ]
]);
$oneDriveDownloadService = new OneDriveDownloadService($httpClient);

// Create the downloader
$providerResolver = new ProviderResolver($httpClient, );
$downloader = new FileDownloader($providerResolver);

// Example URLs to test
$testUrls = [
    'Direct URL' => 'https://httpbin.org/bytes/1024',
    'Direct URL (JPG)' => 'https://httpbin.org/image/jpeg',
    'Direct URL (PDF)' => 'http://www.pdf995.com/samples/pdf.pdf',
    'Direct URL (MP3)' => 'https://filesamples.com/samples/audio/mp3/sample1.mp3',
    'Google Drive 1 (PDF)' => 'https://drive.google.com/file/d/1b-cIS_akKosxT0y7ebQ3VokWXDejWoO-/view?usp=share_link',
    'Google Drive 2 (PDF)' => 'https://drive.google.com/file/d/1b-cIS_akKosxT0y7ebQ3VokWXDejWoO-/view?usp=share_link',
    'One Drive 1 (PDF)' => 'https://onedrive.live.com/embed?cid=a56eb0200f90250e&id=A56EB0200F90250E!s55f78d39d3764c278d43585bf53f1ca7&resid=A56EB0200F90250E!s55f78d39d3764c278d43585bf53f1ca7&ithint=file,pdf&embed=1&migratedtospo=true&redeem=aHR0cHM6Ly8xZHJ2Lm1zL2IvYy9hNTZlYjAyMDBmOTAyNTBlL0lRUTVqZmRWZHRNblRJMURXRnYxUHh5bkFYTU5PTG1hTkVrS1FoYUpBYXRBSV80',
    'One Drive 2 (XLSX)' => 'https://onedrive.live.com/:x:/g/personal/A56EB0200F90250E/EXcLQhDC5-tBkUdSKCAcgE8Bi-DXLRDYwXnwjUziuKsyiQ?resid=A56EB0200F90250E!s10420b77e7c241eb91475228201c804f&ithint=file%2Cxlsx&e=KEl6FC&migratedtospo=true&redeem=aHR0cHM6Ly8xZHJ2Lm1zL3gvYy9hNTZlYjAyMDBmOTAyNTBlL0VYY0xRaERDNS10QmtVZFNLQ0FjZ0U4QmktRFhMUkRZd1hud2pVeml1S3N5aVE_ZT1LRWw2RkM',
    'One Drive 3 (PNG)' => 'https://1drv.ms/i/c/a56eb0200f90250e/EaL9w5yXafVOkHyIWkc_QScB_ud22yZj_Adiq5CsTbyc7A?e=3nkumM',
    'One Drive 4 (MP3)' => 'https://1drv.ms/u/c/a56eb0200f90250e/EZc80xSnNkJHrBqnUu82VGYB5b-SKxb4VOuV-kEAeOJeXg?e=OgOche',
];
echo "Download Library Example\n";
echo "=======================\n\n";

foreach ($testUrls as $provider => $url) {
    echo "Testing {$provider} URL: {$url}\n";
    
    // Check if URL is supported
    if (!$downloader->isSupported($url)) {
        echo "❌ URL not supported\n\n";
        continue;
    }
    
    echo "✅ URL is supported\n";
    
    try {
        // Download the file
        $uploadedFile = $downloader->download($url);
        
        echo "✅ Download successful!\n";
        echo "   - Filename: " . $uploadedFile->getClientOriginalName() . "\n";
        echo "   - Size: " . $uploadedFile->getSize() . " bytes\n";
        echo "   - MIME type: " . $uploadedFile->getMimeType() . "\n";

    } catch (InvalidUrlException $e) {
        echo "❌ Invalid URL: " . $e->getMessage() . "\n";
    } catch (DownloadFailedException $e) {
        echo "❌ Download failed: " . $e->getMessage() . "\n";
    } catch (DownloadException $e) {
        echo "❌ General error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Example completed!\n";
