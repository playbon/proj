<?php

namespace Database\Seeders;

use App\Enums\ArtifactType;
use App\Enums\BuildStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\Channel;
use App\Models\Package;
use App\Models\PackageFile;
use App\Models\PackageVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@configvault.local')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@configvault.local',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]);
        }

        $publisher = User::firstOrCreate(
            ['email' => 'publisher@configvault.local'],
            ['name' => 'Publisher User', 'password' => Hash::make('password'), 'role' => UserRole::Publisher]
        );

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@configvault.local'],
            ['name' => 'Viewer User', 'password' => Hash::make('password'), 'role' => UserRole::Viewer]
        );

        $channels = Channel::all();
        if ($channels->isEmpty()) {
            $this->call(ChannelSeeder::class);
            $channels = Channel::all();
        }

        $packageNames = [
            'nginx-config',
            'redis-cluster',
            'postgres-tuning',
            'docker-compose-stack',
            'kubernetes-manifests',
        ];

        $users = [$admin, $publisher];

        foreach ($packageNames as $i => $name) {
            $package = Package::create([
                'user_id' => $users[$i % 2]->id,
                'name' => $name,
                'slug' => $name,
                'description' => 'Configuration package for ' . str_replace('-', ' ', $name),
                'channel_id' => $channels[$i % $channels->count()]->id,
                'is_active' => true,
            ]);

            $versionCount = ($i % 2 === 0) ? 3 : 2;

            for ($v = 1; $v <= $versionCount; $v++) {
                $version = PackageVersion::create([
                    'package_id' => $package->id,
                    'version' => "1.{$v}.0",
                    'changelog' => "Release {$v} for {$name}",
                    'is_published' => $v < $versionCount,
                    'is_revoked' => false,
                    'published_at' => $v < $versionCount ? now()->subDays($versionCount - $v) : null,
                ]);

                $fileCount = rand(3, 5);
                $storagePath = "packages/{$package->slug}/{$version->version}";

                for ($f = 1; $f <= $fileCount; $f++) {
                    $fileName = "config-{$f}.json";
                    $content = json_encode([
                        'name' => $name,
                        'version' => $version->version,
                        'file' => $f,
                        'settings' => ['key' => 'value-' . $f],
                    ], JSON_PRETTY_PRINT);

                    Storage::disk('local')->put("{$storagePath}/{$fileName}", $content);
                    $fullPath = Storage::disk('local')->path("{$storagePath}/{$fileName}");

                    PackageFile::create([
                        'package_version_id' => $version->id,
                        'original_name' => $fileName,
                        'stored_path' => "{$storagePath}/{$fileName}",
                        'mime_type' => 'application/json',
                        'size' => strlen($content),
                        'sha256' => hash_file('sha256', $fullPath),
                        'sha512' => hash_file('sha512', $fullPath),
                        'verification_status' => VerificationStatus::Valid,
                        'verified_at' => now(),
                    ]);
                }
            }
        }

        $firstPackage = Package::first();
        $firstVersion = $firstPackage->versions()->first();

        for ($b = 0; $b < 2; $b++) {
            $build = Build::create([
                'uuid' => Str::uuid()->toString(),
                'package_version_id' => $firstVersion->id,
                'user_id' => $admin->id,
                'status' => BuildStatus::Completed,
                'started_at' => now()->subMinutes(5 - $b),
                'completed_at' => now()->subMinutes(4 - $b),
            ]);

            $buildDir = "builds/{$build->uuid}";
            $buildDirFull = Storage::disk('local')->path($buildDir);
            if (!is_dir($buildDirFull)) {
                mkdir($buildDirFull, 0755, true);
            }
            $zipPath = $buildDirFull . '/package.zip';

            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            foreach ($firstVersion->files as $file) {
                $fp = Storage::disk('local')->path($file->stored_path);
                if (file_exists($fp)) {
                    $zip->addFile($fp, $file->original_name);
                }
            }
            $zip->close();

            $manifestData = [
                'schema_version' => '1.0',
                'package' => [
                    'name' => $firstPackage->name,
                    'version' => $firstVersion->version,
                    'channel' => $firstPackage->channel->name,
                ],
                'build' => [
                    'id' => $build->uuid,
                    'timestamp' => now()->toIso8601String(),
                    'builder_version' => '1.0.0',
                ],
                'files' => [],
                'integrity' => ['total_files' => 0, 'total_size' => 0, 'manifest_sha256' => ''],
            ];
            $manifestJson = json_encode($manifestData, JSON_PRETTY_PRINT);
            Storage::disk('local')->put("{$buildDir}/manifest.json", $manifestJson);
            $manifestPath = Storage::disk('local')->path("{$buildDir}/manifest.json");

            BuildArtifact::create([
                'build_id' => $build->id,
                'type' => ArtifactType::Zip,
                'file_path' => "{$buildDir}/package.zip",
                'file_size' => filesize($zipPath),
                'sha256' => hash_file('sha256', $zipPath),
            ]);

            BuildArtifact::create([
                'build_id' => $build->id,
                'type' => ArtifactType::Manifest,
                'file_path' => "{$buildDir}/manifest.json",
                'file_size' => filesize($manifestPath),
                'sha256' => hash_file('sha256', $manifestPath),
            ]);
        }
    }
}
