<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuditController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BuildController;
use App\Http\Controllers\Web\BuildReviewController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoleUpgradeController;
use App\Http\Controllers\Web\VerificationController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// ── Authenticated routes ──────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Banned page — accessible even when banned (no not-banned middleware here)
    Route::get('banned', fn () => view('auth.banned'))->name('banned');

    // Logout must be accessible even when banned
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // All other auth routes require non-banned status
    Route::middleware('not-banned')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Ghost verification pages
        Route::get('ghost/pending', [VerificationController::class, 'pendingPage'])->name('ghost.pending');
        Route::get('ghost/chat', [VerificationController::class, 'ghostChat'])->name('ghost.chat');
        Route::post('ghost/chat', [VerificationController::class, 'ghostSendMessage'])->name('ghost.chat.send');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Role upgrade request (viewers only)
        Route::post('upgrade-request', [RoleUpgradeController::class, 'store'])->name('upgrade-request.store')->middleware('not-ghost');

        // Packages — specific routes BEFORE the {package} wildcard
        Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create')->middleware('not-ghost');
        Route::post('packages', [PackageController::class, 'store'])->name('packages.store')->middleware('not-ghost');
        Route::get('packages/{package}', [PackageController::class, 'show'])->name('packages.show');
        Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit')->middleware('not-ghost');
        Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update')->middleware('not-ghost');
        Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy')->middleware('not-ghost');

        // Versions / Files / Builds (write — ghost + viewer blocked via policy)
        Route::post('packages/{package}/versions', [PackageController::class, 'storeVersion'])->name('packages.versions.store')->middleware('not-ghost');
        Route::post('versions/{version}/files', [PackageController::class, 'uploadFiles'])->name('versions.files.upload')->middleware('not-ghost');
        Route::delete('files/{file}', [PackageController::class, 'deleteFile'])->name('files.destroy')->middleware('not-ghost');
        Route::post('versions/{version}/build', [BuildController::class, 'store'])->name('builds.store')->middleware('not-ghost');

        // Builds (read — everyone)
        Route::get('builds', [BuildController::class, 'index'])->name('builds.index');
        Route::get('builds/{build}', [BuildController::class, 'show'])->name('builds.show');
        Route::get('artifacts/{artifact}/download', [BuildController::class, 'download'])->name('artifacts.download');

        // Reports (non-ghost users)
        Route::post('builds/{build}/report', [ReportController::class, 'store'])->name('builds.report')->middleware('not-ghost');
        Route::post('packages/{package}/report', [ReportController::class, 'storePackage'])->name('packages.report')->middleware('not-ghost');

        // ── Admin + Creator routes ────────────────────────────
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('audit/export', [AuditController::class, 'export'])->name('audit.export');
            Route::get('queues', [AdminController::class, 'queues'])->name('queues');
            Route::get('users', [AdminController::class, 'users'])->name('users');
            Route::put('users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
            Route::resource('channels', AdminController::class)->only(['index', 'store', 'destroy']);

            // Verification
            Route::get('verification', [VerificationController::class, 'index'])->name('verification.index');
            Route::post('verification/{verificationRequest}/approve', [VerificationController::class, 'approve'])->name('verification.approve');
            Route::post('verification/{verificationRequest}/reject', [VerificationController::class, 'reject'])->name('verification.reject');
            Route::get('verification/{verificationRequest}/chat', [VerificationController::class, 'chat'])->name('verification.chat');
            Route::post('verification/{verificationRequest}/chat', [VerificationController::class, 'sendMessage'])->name('verification.message');
            Route::post('verification/{verificationRequest}/confirm', [VerificationController::class, 'finalApprove'])->name('verification.confirm');

            // Build reports
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::post('reports/{report}/accept', [ReportController::class, 'accept'])->name('reports.accept');
            Route::post('reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

            // Package reports
            Route::get('package-reports', [ReportController::class, 'indexPackages'])->name('package-reports.index');
            Route::post('package-reports/{report}/accept', [ReportController::class, 'acceptPackage'])->name('package-reports.accept');
            Route::post('package-reports/{report}/reject', [ReportController::class, 'rejectPackage'])->name('package-reports.reject');

            // Build review (first-time publisher builds)
            Route::get('build-review', [BuildReviewController::class, 'index'])->name('build-review.index');
            Route::post('build-review/{build}/approve', [BuildReviewController::class, 'approve'])->name('build-review.approve');
            Route::post('build-review/{build}/reject', [BuildReviewController::class, 'reject'])->name('build-review.reject');

            // Role upgrade requests
            Route::get('upgrade-requests', [RoleUpgradeController::class, 'index'])->name('upgrade-requests.index');
            Route::post('upgrade-requests/{upgradeRequest}/approve', [RoleUpgradeController::class, 'approve'])->name('upgrade-requests.approve');
            Route::post('upgrade-requests/{upgradeRequest}/reject', [RoleUpgradeController::class, 'reject'])->name('upgrade-requests.reject');
        });

    }); // end not-banned
});
