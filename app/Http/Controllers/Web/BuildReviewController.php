<?php

namespace App\Http\Controllers\Web;

use App\Enums\BuildStatus;
use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuildReviewController extends Controller
{
    public function index(): View
    {
        $builds = Build::with(['packageVersion.package', 'user'])
            ->where('status', BuildStatus::PendingReview)
            ->latest()
            ->paginate(20);

        return view('admin.build_review', compact('builds'));
    }

    public function approve(Build $build): RedirectResponse
    {
        if ($build->status !== BuildStatus::PendingReview) {
            return back()->with('error', 'Сборка не ожидает проверки.');
        }

        $build->update(['status' => BuildStatus::Completed]);

        return back()->with('success', 'Сборка одобрена и опубликована.');
    }

    public function reject(Build $build): RedirectResponse
    {
        if ($build->status !== BuildStatus::PendingReview) {
            return back()->with('error', 'Сборка не ожидает проверки.');
        }

        $build->update(['status' => BuildStatus::Blocked]);

        return back()->with('success', 'Сборка отклонена и заблокирована.');
    }
}
