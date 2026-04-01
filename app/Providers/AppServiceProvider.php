<?php

namespace App\Providers;

use App\Events\BuildCompleted;
use App\Events\BuildFailed;
use App\Events\PackageDownloaded;
use App\Events\PackageVerified;
use App\Listeners\LogBuildCompleted;
use App\Listeners\LogBuildFailed;
use App\Listeners\LogPackageVerified;
use App\Listeners\RecordDownloadStat;
use App\Models\Build;
use App\Models\Package;
use App\Policies\BuildPolicy;
use App\Policies\PackagePolicy;
use App\Services\BuildService;
use App\Services\DistributionService;
use App\Services\HashService;
use App\Services\ManifestService;
use App\Services\PackageService;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HashService::class);
        $this->app->singleton(PackageService::class);
        $this->app->singleton(VerificationService::class);
        $this->app->singleton(BuildService::class);
        $this->app->singleton(ManifestService::class);
        $this->app->singleton(DistributionService::class);
    }

    public function boot(): void
    {
        Gate::policy(Package::class, PackagePolicy::class);
        Gate::policy(Build::class, BuildPolicy::class);

        Event::listen(BuildCompleted::class, LogBuildCompleted::class);
        Event::listen(BuildFailed::class, LogBuildFailed::class);
        Event::listen(PackageVerified::class, LogPackageVerified::class);
        Event::listen(PackageDownloaded::class, RecordDownloadStat::class);
    }
}
