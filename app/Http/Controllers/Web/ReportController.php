<?php

namespace App\Http\Controllers\Web;

use App\Enums\BuildStatus;
use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\BuildReport;
use App\Models\Package;
use App\Models\PackageReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    // ── Build reports ─────────────────────────────────────────

    public function store(Request $request, Build $build): RedirectResponse
    {
        if ($build->user->isCreator()) {
            return back()->with('error', 'Нельзя пожаловаться на сборку создателя.');
        }

        if ($build->status === BuildStatus::Blocked) {
            return back()->with('error', 'Сборка уже заблокирована.');
        }

        $existing = BuildReport::where('build_id', $build->id)
            ->where('reporter_id', auth()->id())
            ->exists();
        if ($existing) {
            return back()->with('error', 'Вы уже отправили жалобу на эту сборку.');
        }

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        BuildReport::create([
            'build_id'    => $build->id,
            'reporter_id' => auth()->id(),
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Жалоба отправлена администратору.');
    }

    public function index(): View
    {
        $reports = BuildReport::with(['build.packageVersion.package', 'build.user', 'reporter'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.reports', compact('reports'));
    }

    public function accept(BuildReport $report): RedirectResponse
    {
        $report->build->update(['status' => BuildStatus::Blocked]);

        $publisher = $report->build->user;
        if (!$publisher->isCreator() && !$publisher->isAdmin()) {
            $publisher->ban();
        }

        $report->update(['status' => 'accepted', 'reviewed_by' => auth()->id()]);

        BuildReport::where('build_id', $report->build_id)
            ->where('id', '!=', $report->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Жалоба принята. Сборка заблокирована, издатель получил бан.');
    }

    public function reject(BuildReport $report): RedirectResponse
    {
        $report->update(['status' => 'rejected', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Жалоба отклонена.');
    }

    // ── Package reports ───────────────────────────────────────

    public function storePackage(Request $request, Package $package): RedirectResponse
    {
        if ($package->user->isCreator()) {
            return back()->with('error', 'Нельзя пожаловаться на пакет создателя.');
        }

        if (!$package->is_active) {
            return back()->with('error', 'Пакет уже деактивирован.');
        }

        $existing = PackageReport::where('package_id', $package->id)
            ->where('reporter_id', auth()->id())
            ->exists();
        if ($existing) {
            return back()->with('error', 'Вы уже отправили жалобу на этот пакет.');
        }

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        PackageReport::create([
            'package_id'  => $package->id,
            'reporter_id' => auth()->id(),
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Жалоба отправлена администратору.');
    }

    public function indexPackages(): View
    {
        $reports = PackageReport::with(['package.user', 'reporter'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.package_reports', compact('reports'));
    }

    public function acceptPackage(PackageReport $report): RedirectResponse
    {
        $report->package->update(['is_active' => false]);

        $owner = $report->package->user;
        if (!$owner->isCreator() && !$owner->isAdmin()) {
            $owner->ban();
        }

        $report->update(['status' => 'accepted', 'reviewed_by' => auth()->id()]);

        PackageReport::where('package_id', $report->package_id)
            ->where('id', '!=', $report->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Жалоба принята. Пакет деактивирован, владелец получил бан.');
    }

    public function rejectPackage(PackageReport $report): RedirectResponse
    {
        $report->update(['status' => 'rejected', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Жалоба отклонена.');
    }
}
