<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\BuildStatus;
use App\Enums\VerificationStatus;
use App\Models\Build;
use App\Models\DownloadStat;
use App\Models\Package;
use App\Models\PackageFile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPackages = Package::count();
        $activeBuilds = Build::whereIn('status', [BuildStatus::Queued, BuildStatus::Processing])->count();
        $verificationErrors = PackageFile::where('verification_status', VerificationStatus::Invalid)->count();
        $weeklyDownloads = DownloadStat::where('downloaded_at', '>=', now()->subWeek())->count();

        $recentBuilds = Build::with(['packageVersion.package', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentFiles = PackageFile::with('packageVersion.package')
            ->latest()
            ->take(5)
            ->get();

        $buildChartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $buildChartData[] = [
                'date' => $date,
                'count' => Build::whereDate('created_at', $date)->count(),
            ];
        }

        return view('dashboard', compact(
            'totalPackages',
            'activeBuilds',
            'verificationErrors',
            'weeklyDownloads',
            'recentBuilds',
            'recentFiles',
            'buildChartData'
        ));
    }
}
