<?php

namespace CodingTask\Download\ValueObject;

use CodingTask\Download\Exceptions\InvalidUrlException;

class ParsedUrl
{
    public function __construct(
        public readonly string $scheme,
        public readonly string $host,
        public readonly string $path,
        public readonly ?string $query,
    ) {}

    public static function fromString(string $url): self
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme']) || !in_array($parts['scheme'], ['http', 'https'])) {
            throw new InvalidUrlException('Invalid URL scheme');
        }

        return new self(
            scheme: strtolower($parts['scheme']),
            host: strtolower($parts['host'] ?? ''),
            path: $parts['path'] ?? '',
            query: $parts['query'] ?? null
        );
    }
}