<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\PackageVersion;
use App\Services\BuildService;
use App\Services\DistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuildController extends Controller
{
    public function __construct(
        protected BuildService $buildService,
        protected DistributionService $distributionService
    ) {
    }

    public function store(Request $request, PackageVersion $version): JsonResponse
    {
        if ($version->files()->count() === 0) {
            return response()->json(['message' => 'Cannot build a version with no files.'], 422);
        }

        $build = $this->buildService->initiate($version, $request->user());

        return response()->json($build, 201);
    }

    public function show(Build $build): JsonResponse
    {
        return response()->json($build->load(['packageVersion.package', 'user', 'artifacts']));
    }

    public function download(Build $build): StreamedResponse|JsonResponse
    {
        $artifact = $build->artifacts()->where('type', 'zip')->first();

        if (!$artifact) {
            return response()->json(['message' => 'No artifact available.'], 404);
        }

        return $this->distributionService->download(
            $artifact,
            auth()->user(),
            request()->ip()
        );
    }

    public function manifest(Build $build): JsonResponse
    {
        $artifact = $build->artifacts()->where('type', 'manifest')->first();

        if (!$artifact) {
            return response()->json(['message' => 'No manifest available.'], 404);
        }

        $path = storage_path('app/' . $artifact->file_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Manifest file not found.'], 404);
        }

        return response()->json(json_decode(file_get_contents($path), true));
    }
}
