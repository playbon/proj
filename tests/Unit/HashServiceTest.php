<?php

namespace Tests\Unit;

use App\Services\HashService;
use Tests\TestCase;

class HashServiceTest extends TestCase
{
    private HashService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HashService();
    }

    // ── computeFromContent ────────────────────────────────────

    public function test_compute_sha256_from_content_matches_php_hash(): void
    {
        $content  = 'hello configvault';
        $expected = hash('sha256', $content);
        $this->assertEquals($expected, $this->service->computeFromContent($content, 'sha256'));
    }

    public function test_compute_sha512_from_content_matches_php_hash(): void
    {
        $content  = 'hello configvault';
        $expected = hash('sha512', $content);
        $this->assertEquals($expected, $this->service->computeFromContent($content, 'sha512'));
    }

    public function test_default_algo_is_sha256(): void
    {
        $content = 'test';
        $this->assertEquals(
            hash('sha256', $content),
            $this->service->computeFromContent($content)
        );
    }

    public function test_empty_string_produces_known_sha256(): void
    {
        $expected = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
        $this->assertEquals($expected, $this->service->computeFromContent(''));
    }

    public function test_different_contents_produce_different_hashes(): void
    {
        $h1 = $this->service->computeFromContent('aaa');
        $h2 = $this->service->computeFromContent('bbb');
        $this->assertNotEquals($h1, $h2);
    }

    // ── computeSha256 / computeSha512 from file ───────────────

    public function test_compute_sha256_from_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'file content');

        $this->assertEquals(hash_file('sha256', $path), $this->service->computeSha256($path));
        unlink($path);
    }

    public function test_compute_sha512_from_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'file content');

        $this->assertEquals(hash_file('sha512', $path), $this->service->computeSha512($path));
        unlink($path);
    }

    public function test_sha256_and_sha512_of_same_file_differ(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'content');

        $this->assertNotEquals(
            $this->service->computeSha256($path),
            $this->service->computeSha512($path)
        );
        unlink($path);
    }

    // ── verify ────────────────────────────────────────────────

    public function test_verify_returns_true_for_correct_hash(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'verify me');

        $hash = hash_file('sha256', $path);
        $this->assertTrue($this->service->verify($path, $hash, 'sha256'));
        unlink($path);
    }

    public function test_verify_returns_false_for_wrong_hash(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'verify me');

        $this->assertFalse($this->service->verify($path, 'deadbeef', 'sha256'));
        unlink($path);
    }

    public function test_verify_returns_false_after_file_content_changed(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'original content');
        $hash = hash_file('sha256', $path);

        file_put_contents($path, 'tampered content');
        $this->assertFalse($this->service->verify($path, $hash, 'sha256'));
        unlink($path);
    }

    public function test_verify_with_sha512_algo(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($path, 'sha512 test');

        $hash = hash_file('sha512', $path);
        $this->assertTrue($this->service->verify($path, $hash, 'sha512'));
        unlink($path);
    }
}
