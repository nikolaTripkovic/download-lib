<?php

namespace CodingTask\Download\ValueObject;

class FileInfo
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $contentType
    ) {}

}