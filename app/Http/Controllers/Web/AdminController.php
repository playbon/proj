<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChannelRequest;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function queues(): View
    {
        $pendingJobs = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->get();

        $failedJobs = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->get();

        return view('admin.queues', compact('pendingJobs', 'failedJobs'));
    }

    public function users(): View
    {
        $users = User::latest()->paginate(20);

        // Online = last seen within 5 minutes
        $onlineIds = User::whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->pluck('id')
            ->toArray();

        return view('admin.users', compact('users', 'onlineIds'));
    }

    public function index(): View
    {
        $channels = Channel::withCount('packages')->get();

        return view('admin.channels', compact('channels'));
    }

    public function store(StoreChannelRequest $request): RedirectResponse
    {
        Channel::create($request->validated());

        return redirect()->route('admin.channels.index')->with('success', 'Канал создан.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        $channel->delete();

        return redirect()->route('admin.channels.index')->with('success', 'Канал удалён.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Нельзя изменить собственную роль.');
        }

        $request->validate([
            'role' => ['required', 'in:creator,admin,publisher,viewer,ghost'],
        ]);

        $newRole = $request->role;

        // Only creator can promote to admin/creator or demote from admin
        if (in_array($newRole, ['admin', 'creator']) && !$currentUser->isCreator()) {
            return back()->with('error', 'Только создатель может назначать администраторов.');
        }

        // Only creator can change role of another admin/creator
        if ($user->isAdmin() && !$currentUser->isCreator()) {
            return back()->with('error', 'Только создатель может изменять роль администратора.');
        }

        $user->update(['role' => UserRole::from($newRole)]);

        return back()->with('success', 'Роль пользователя «' . $user->name . '» изменена.');
    }
}
