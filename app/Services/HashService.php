<?php

namespace App\Services;

class HashService
{
    public function computeSha256(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    public function computeSha512(string $filePath): string
    {
        return hash_file('sha512', $filePath);
    }

    public function computeFromContent(string $content, string $algo = 'sha256'): string
    {
        return hash($algo, $content);
    }

    public function verify(string $filePath, string $expectedHash, string $algo = 'sha256'): bool
    {
        return hash_file($algo, $filePath) === $expectedHash;
    }
}
