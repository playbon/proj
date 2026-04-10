<?php

namespace App\Http\Controllers\Web;

use App\Enums\BuildStatus;
use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\PackageVersion;
use App\Services\BuildService;
use App\Services\DistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuildController extends Controller
{
    public function __construct(
        protected BuildService $buildService,
        protected DistributionService $distributionService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Build::with(['packageVersion.package', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $builds = $query->latest()->paginate(15);

        return view('builds.index', compact('builds'));
    }

    public function store(Request $request, PackageVersion $version): RedirectResponse
    {
        $this->authorize('update', $version->package);

        if ($version->files()->count() === 0) {
            return back()->with('error', 'Нельзя запустить сборку для версии без файлов.');
        }

        $build = $this->buildService->initiate($version, $request->user());

        return redirect()->route('builds.show', $build)->with('success', 'Сборка добавлена в очередь.');
    }

    public function show(Build $build): View
    {
        $build->load(['packageVersion.package', 'user', 'artifacts']);

        $manifest = null;
        $manifestArtifact = $build->artifacts->firstWhere('type.value', 'manifest');
        if ($manifestArtifact) {
            $path = storage_path('app/' . $manifestArtifact->file_path);
            if (file_exists($path)) {
                $manifest = json_decode(file_get_contents($path), true);
            }
        }

        return view('builds.show', compact('build', 'manifest'));
    }

    public function download(BuildArtifact $artifact): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();

        // Ghosts cannot download until verified
        if ($user->isGhost()) {
            abort(403, 'Скачивание недоступно до прохождения верификации.');
        }

        $build = $artifact->build;

        // Blocked or pending-review builds are not downloadable
        if (in_array($build->status, [BuildStatus::Blocked, BuildStatus::PendingReview])) {
            abort(403, 'Скачивание недоступно: сборка заблокирована или ожидает проверки.');
        }

        return $this->distributionService->download(
            $artifact,
            $user,
            request()->ip()
        );
    }
}
