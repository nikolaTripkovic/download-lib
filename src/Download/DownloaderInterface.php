<?php

namespace CodingTask\Download;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DownloaderInterface
{
    public function download(string $url): UploadedFile;

}