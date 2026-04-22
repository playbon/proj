<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\RoleUpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleUpgradeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== UserRole::Viewer) {
            return back()->with('error', 'Только наблюдатели могут запросить повышение роли.');
        }

        $existing = RoleUpgradeRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($existing) {
            return back()->with('error', 'У вас уже есть активный запрос на повышение роли.');
        }

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        RoleUpgradeRequest::create([
            'user_id' => $user->id,
            'reason'  => $request->reason,
            'status'  => 'pending',
        ]);

        return back()->with('success', 'Запрос отправлен администратору. Ожидайте ответа.');
    }

    public function index(): View
    {
        $requests = RoleUpgradeRequest::with(['user', 'reviewer'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.upgrade_requests', compact('requests'));
    }

    public function approve(RoleUpgradeRequest $upgradeRequest): RedirectResponse
    {
        $upgradeRequest->user->update(['role' => UserRole::Publisher]);
        $upgradeRequest->update(['status' => 'approved', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Запрос одобрен. Пользователь повышен до публикатора.');
    }

    public function reject(RoleUpgradeRequest $upgradeRequest): RedirectResponse
    {
        $upgradeRequest->update(['status' => 'rejected', 'reviewed_by' => auth()->id()]);

        return back()->with('success', 'Запрос отклонён.');
    }
}
