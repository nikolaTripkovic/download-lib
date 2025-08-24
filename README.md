# Download Library

A production-ready PHP library for downloading public files from various cloud storage providers (Google Drive, OneDrive) and regular public URLs. Built with clean architecture, SOLID principles, and extensible design patterns.

## 🚀 Features

- **Multi-provider support**: Google Drive, OneDrive, and direct HTTP/HTTPS URLs
- **Extensible architecture**: Easy to add new providers (Dropbox, Box, etc.)
- **Clean API**: Simple interface returning Symfony's `UploadedFile`
- **Comprehensive error handling**: Specific exception hierarchy with meaningful error messages
- **Automatic file extension detection**: Based on MIME types with proper file naming
- **SOLID principles**: Well-structured, maintainable code following design patterns
- **Security**: URL validation, path traversal protection, secure file handling
- **Performance**: Efficient HTTP client reuse, proper resource management

## 📋 Requirements

- PHP 8.2+
- Composer (for dependency management)

## 🛠️ Installation

```bash
# Clone the repository
git clone git@github.com:nikolaTripkovic/download-lib.git

# Install dependencies
composer install

# Run the example to test
php example.php
```

## 🚀 Quick Start

```php
use CodingTask\Download\FileDownloader;
use CodingTask\Download\Resolver\ProviderResolver;
use CodingTask\Download\Exceptions\DownloadException;
use Symfony\Component\HttpClient\HttpClient;

// Create HTTP client with reasonable defaults
$httpClient = HttpClient::create([
    'timeout' => 30,
    'max_redirects' => 5,
    'headers' => [
        'User-Agent' => 'DownloadLib/1.0'
    ]
]);

// Create the downloader
$providerResolver = new ProviderResolver($httpClient);
$downloader = new FileDownloader($providerResolver);

// Download a file
try {
    $uploadedFile = $downloader->download('https://example.com/file.pdf');
    
    // Use the file
    echo "Downloaded: " . $uploadedFile->getClientOriginalName();
    echo "Size: " . $uploadedFile->getSize() . " bytes";
    echo "MIME type: " . $uploadedFile->getMimeType();
    echo "File path: " . $uploadedFile->getPathname();
    
} catch (DownloadException $e) {
    echo "Download failed: " . $e->getMessage();
}
```

## 🔗 Supported URL Formats

### Google Drive
- `https://drive.google.com/file/d/FILE_ID/view`
- `https://drive.google.com/open?id=FILE_ID`
- `https://drive.google.com/file/d/FILE_ID/view?usp=share_link`

> **⚠️ Important Notes**: 
> - Google Drive public links may not work reliably due to Google's frequent API changes and security restrictions
> - Google Docs URLs (`docs.google.com/document/`) are **not supported** for direct download
> - For production use, consider using Google Drive API with authentication

### OneDrive
- `https://1drv.ms/u/s!SHARE_ID`
- `https://onedrive.live.com/redir?resid=FILE_ID`
- `https://1drv.ms/f/s!SHARE_ID`
- `https://1drv.ms/x/c/SHARE_ID/FILE_ID`
- `https://onedrive.live.com/embed?cid=...`

> **⚠️ Important Notes**: OneDrive downloader automatically:
> - Follows redirects and extracts actual download URLs from HTML/JavaScript responses
> - Handles various OneDrive URL formats and transformations
> - Supports all file types that OneDrive allows for public sharing
> - May require specific sharing permissions and may not work reliably due to Microsoft's security restrictions

### Direct URLs
- `https://example.com/file.pdf`
- `http://example.com/image.jpg`
- Any standard HTTP/HTTPS URL

## 📁 Supported File Types

The library automatically detects and adds proper file extensions based on MIME types:

**Images**: JPEG, PNG, GIF, WebP, SVG
**Documents**: PDF, JSON, XML, HTML, CSS, JavaScript
**Office Files**: Excel (.xlsx, .xls), Word (.docx, .doc), PowerPoint (.pptx, .ppt)
**Archives**: ZIP, RAR, 7Z
**Text**: Plain text (.txt), Markdown (.md)
**Audio/Video**: MP3, MP4, AVI, MOV

## 🏗️ Architecture

The library follows a clean, extensible architecture based on SOLID principles:

```
FileDownloader (Main Service)
    ↓
ProviderResolver (URL Detection & Factory)
    ↓
Specific Providers:
├── GoogleDriveDownloader
├── OneDriveDownloader
└── DirectDownloader
```

### Project Structure

```
src/
├── Download/
│   ├── Exception/
│   │   ├── DownloadException.php (base exception)
│   │   ├── DownloadFailedException.php (download failures)
│   │   ├── InvalidUrlException.php (URL validation errors)
│   │   └── FileException.php (file processing errors)
│   ├── Providers/
│   │   ├── AbstractDownloader.php (common functionality)
│   │   ├── DirectDownloader.php (HTTP/HTTPS URLs)
│   │   ├── GoogleDriveDownloader.php (Google Drive)
│   │   └── OneDriveDownloader.php (OneDrive)
│   ├── Resolver/
│   │   └── ProviderResolver.php (URL detection & factory)
│   ├── Services/
│   │   └── OneDriveDownloadService.php (OneDrive specific logic)
│   ├── Util/
│   │   ├── FileUtils.php (file utilities)
│   │   └── StreamDownloader.php (streaming downloads)
│   ├── ValueObject/
│   │   ├── FileInfo.php (file information)
│   │   └── ParsedUrl.php (parsed URL data)
│   ├── DownloaderInterface.php (provider contract)
│   └── FileDownloader.php (main service)
└── Kernel.php
```

### Design Patterns Used

1. **Strategy Pattern**: Each provider implements `DownloaderInterface`
2. **Factory Pattern**: `ProviderResolver` creates appropriate downloaders
3. **Template Method Pattern**: `AbstractDownloader` provides common functionality
4. **Value Object Pattern**: Immutable data structures (`FileInfo`, `ParsedUrl`)
5. **Dependency Injection**: Constructor injection for all dependencies

## 🔌 Adding New Providers

To add support for a new provider (e.g., Dropbox):

1. Create a new provider class implementing `DownloaderInterface`
2. Add detection logic to `ProviderResolver`
3. Register the provider in your dependency injection container

## 🧪 Testing

Run the example to test the library:

```bash
php example.php
```

The example will test downloading files from various sources and display the results.

## 📋 Explanation Document

This document provides a detailed explanation of the architectural decisions, implementation approach, and future considerations for the Download Library.

### 1. Architecture Overview

#### Why did you choose this structure?

**Separation of Concerns**: The library is structured to separate different responsibilities:
- **FileDownloader**: Main service entry point
- **ProviderResolver**: URL detection and provider factory
- **Individual Providers**: Specific implementation for each cloud service
- **Value Objects**: Immutable data structures for type safety
- **Exception Hierarchy**: Specific error types for different scenarios

**Benefits of this structure:**
- **Maintainability**: Each class has a single responsibility
- **Testability**: Components can be tested in isolation
- **Extensibility**: New providers can be added without modifying existing code
- **Readability**: Clear code organization makes it easy to understand

#### What design patterns did you use and why?

1. **Strategy Pattern** (`DownloaderInterface`):
   - **Why**: Each provider (Google Drive, OneDrive, Direct) has different download logic
   - **Benefit**: Allows runtime selection of download strategy
   - **Implementation**: All providers implement the same interface

2. **Factory Pattern** (`ProviderResolver`):
   - **Why**: Need to create appropriate downloader based on URL analysis
   - **Benefit**: Encapsulates object creation logic
   - **Implementation**: Analyzes URL and returns appropriate provider instance

3. **Template Method Pattern** (`AbstractDownloader`):
   - **Why**: Common download logic shared across providers
   - **Benefit**: Reduces code duplication and ensures consistent behavior
   - **Implementation**: Abstract class with common methods, concrete classes implement specifics

4. **Value Object Pattern** (`FileInfo`, `ParsedUrl`):
   - **Why**: Need immutable data structures for file information and parsed URLs
   - **Benefit**: Type safety and prevents accidental modifications
   - **Implementation**: Immutable objects with validation

5. **Dependency Injection**:
   - **Why**: Components need HTTP client and other dependencies
   - **Benefit**: Loose coupling, easier testing, flexible configuration
   - **Implementation**: Constructor injection throughout the codebase

#### How does your solution support future extensibility?

**Interface-Based Design**: All providers implement `DownloaderInterface`, making it easy to add new providers:
```php
class DropboxDownloader implements DownloaderInterface
{
    public function download(string $url): UploadedFile
    {
        // Dropbox-specific implementation
    }
}
```

**Provider Resolution**: `ProviderResolver` can easily detect new providers:
```php
if (str_contains($host, 'dropbox.com')) {
    return new DropboxDownloader($this->httpClient);
}
```

**No Code Modification**: Adding new providers doesn't require changes to existing code, following the Open/Closed Principle.

### 2. Implementation Details

#### How do you handle different URL formats?

**Google Drive URLs**:
- Detects URLs containing `google.com` and `/file/d/`
- Extracts file ID using regex patterns
- **Excludes Google Docs URLs** which are not supported for direct download

**OneDrive URLs**:
- Detects URLs from `1drv.ms` or `onedrive.live.com`
- Handles various URL formats (u/, f/, redir, x/c/, embed)
- Supports all file types that OneDrive allows for public sharing

**Direct URLs**:
- Fallback for all other HTTP/HTTPS URLs
- Handles redirects and various content types
- Extracts filename from Content-Disposition header or URL path
- Supports any standard HTTP/HTTPS URL

**URL Detection Strategy**:
```php
// In ProviderResolver
if (str_contains($host, 'google.com') && str_contains($path, '/file/d/')) {
    return new GoogleDriveDownloader($this->httpClient);
} elseif (str_contains($host, '1drv.ms') || str_contains($host, 'onedrive.live.com')) {
    return new OneDriveDownloader($this->httpClient);
} else {
    return new DirectDownloader($this->httpClient);
}
```

#### What's your approach to error handling?

**Exception Hierarchy**:
```
DownloadException (base)
├── InvalidUrlException (URL validation errors)
├── DownloadFailedException (network/server errors)
└── FileException (file processing errors)
```

**Graceful Degradation**: The library handles various failure scenarios:
- **Invalid URLs** → `InvalidUrlException` with clear error messages
- **Network failures** → `DownloadFailedException` with HTTP status codes
- **Server errors** → `DownloadFailedException` with server response details
- **File processing errors** → `FileException` with file-specific error information

**Resource Management**: Temporary files are properly cleaned up even when exceptions occur using try-finally blocks.

**Meaningful Messages**: Each exception provides clear, actionable error messages:
```php
throw new InvalidUrlException("Unsupported URL format for Google Drive: {$url}");
throw new DownloadFailedException("HTTP request failed with status {$statusCode}");
```

### 3. Future Considerations

#### How would you add support for Dropbox?

**Step 1: Create Dropbox Provider**
```php
class DropboxDownloader implements DownloaderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {}

    public function download(string $url): UploadedFile
    {
        $fileId = $this->extractDropboxId($url);
        $downloadUrl = "https://dl.dropboxusercontent.com/s/{$fileId}/filename";
        
        // Implementation similar to other providers
        return $this->downloadFile($downloadUrl);
    }
    
    private function extractDropboxId(string $url): ?string
    {
        // Extract from dropbox.com URLs
        if (preg_match('/\/s\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
```

**Step 2: Add Detection Logic**
```php
// In ProviderResolver
if (str_contains($host, 'dropbox.com')) {
    return new DropboxDownloader($this->httpClient);
}
```

#### What would you improve given more time?

1. **Google Drive large files**: Direct download of large files that require a manual click on the download button
2. **Unit Tests**: Comprehensive test coverage
3. **Configuration System**: Environment-based settings for timeouts, user agents
4. **Logging**: Structured logging for debugging
5. **Retry Logic**: Exponential backoff for failed requests
6. **Authentication Support**: OAuth2 for private files

#### How would you handle authentication for private files?

**OAuth2 Implementation Approach**:

The authentication system would be built around OAuth2 flow with the following components:

1. **AuthenticatedDownloaderInterface**: An extension of the base interface that adds authentication capabilities, including methods to set credentials and check authentication status.

2. **Authenticated Provider Classes**: Each provider (Google Drive, OneDrive, etc.) would have an authenticated version that extends the base downloader and implements OAuth2 authentication. These classes would:
   - Accept OAuth2 tokens (access_token, refresh_token)
   - Add Authorization headers to HTTP requests
   - Handle token refresh when access tokens expire
   - Validate authentication before attempting downloads

3. **Credential Management**: A dedicated service to handle OAuth2 credentials:
   - Load credentials from environment variables or secure storage
   - Manage token refresh cycles
   - Store and retrieve access/refresh tokens per provider
   - Handle different authentication scopes for different providers

4. **Usage Flow**: The library would detect when authentication is required and automatically:
   - Check if valid credentials are available
   - Use the appropriate authenticated downloader
   - Handle authentication errors gracefully
   - Provide clear error messages when authentication fails

This approach maintains the existing interface while adding authentication capabilities through composition and interface extension, following the Open/Closed Principle.