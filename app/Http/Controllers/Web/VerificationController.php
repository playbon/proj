<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationMessage;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    // Admin/Creator: list pending requests
    public function index(): View
    {
        $user = auth()->user();

        $requests = VerificationRequest::with(['user', 'assignee'])
            ->whereIn('status', ['pending', 'chatting'])
            ->when(!$user->isCreator(), fn($q) => $q->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)->orWhereNull('assigned_to');
            }))
            ->latest()
            ->get();

        return view('admin.verification', compact('requests'));
    }

    // Admin/Creator: approve request → open chat
    public function approve(VerificationRequest $verificationRequest): RedirectResponse
    {
        $verificationRequest->update([
            'status'      => 'chatting',
            'assigned_to' => auth()->id(),
        ]);

        return redirect()->route('admin.verification.chat', $verificationRequest)
            ->with('success', 'Чат открыт. Верифицируйте пользователя.');
    }

    // Admin/Creator: reject → reassign to another online admin
    public function reject(VerificationRequest $verificationRequest): RedirectResponse
    {
        $currentId = auth()->id();

        $next = User::where('role', UserRole::Admin)
            ->where('id', '!=', $currentId)
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->inRandomOrder()
            ->first();

        if (!$next) {
            $next = User::where('role', UserRole::Creator)
                ->where('id', '!=', $currentId)
                ->where('last_seen_at', '>=', now()->subMinutes(2))
                ->first();
        }

        $verificationRequest->update([
            'status'      => 'pending',
            'assigned_to' => $next?->id,
        ]);

        return back()->with('success', $next
            ? 'Запрос передан администратору «' . $next->name . '».'
            : 'Нет онлайн-администраторов. Запрос помещён в очередь создателя.');
    }

    // Admin/Creator: chat view
    public function chat(VerificationRequest $verificationRequest): View
    {
        $this->authorizeChatAccess($verificationRequest);
        $verificationRequest->load(['user', 'messages.sender']);

        return view('admin.verification_chat', ['vr' => $verificationRequest]);
    }

    // Admin/Creator: send a message
    public function sendMessage(Request $request, VerificationRequest $verificationRequest): RedirectResponse
    {
        $this->authorizeChatAccess($verificationRequest);
        $request->validate(['body' => 'required|string|max:2000']);

        VerificationMessage::create([
            'request_id' => $verificationRequest->id,
            'user_id'    => auth()->id(),
            'body'       => $request->body,
        ]);

        return back();
    }

    // Admin/Creator: final approve → user becomes viewer
    public function finalApprove(VerificationRequest $verificationRequest): RedirectResponse
    {
        $this->authorizeChatAccess($verificationRequest);

        $verificationRequest->user->update(['role' => UserRole::Viewer]);
        $verificationRequest->update(['status' => 'approved']);

        return redirect()->route('admin.verification.index')
            ->with('success', 'Пользователь «' . $verificationRequest->user->name . '» верифицирован.');
    }

    // Ghost: pending status page
    public function pendingPage(): View
    {
        $vr = auth()->user()->verificationRequest;
        return view('ghost.pending', compact('vr'));
    }

    // Ghost: chat view (after admin opens chat)
    public function ghostChat(): View|RedirectResponse
    {
        $vr = auth()->user()->verificationRequest;

        if (!$vr || $vr->status !== 'chatting') {
            return redirect()->route('ghost.pending');
        }

        $vr->load('messages.sender');
        return view('ghost.chat', compact('vr'));
    }

    // Ghost: send message in chat
    public function ghostSendMessage(Request $request): RedirectResponse
    {
        $vr = auth()->user()->verificationRequest;

        abort_if(!$vr || $vr->status !== 'chatting', 403);

        $request->validate(['body' => 'required|string|max:2000']);

        VerificationMessage::create([
            'request_id' => $vr->id,
            'user_id'    => auth()->id(),
            'body'       => $request->body,
        ]);

        return back();
    }

    private function authorizeChatAccess(VerificationRequest $vr): void
    {
        $user = auth()->user();
        if (!$user->isCreator() && $user->role !== UserRole::Admin) {
            abort(403);
        }
    }
}
