<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVersionRequest;
use App\Models\Package;
use App\Models\PackageVersion;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;

class VersionController extends Controller
{
    public function __construct(protected PackageService $packageService)
    {
    }

    public function index(Package $package): JsonResponse
    {
        $versions = $package->versions()->with('files')->latest()->paginate(15);

        return response()->json($versions);
    }

    public function store(StoreVersionRequest $request, Package $package): JsonResponse
    {
        $version = $this->packageService->createVersion($package, $request->validated());

        return response()->json($version->load('files'), 201);
    }

    public function show(PackageVersion $version): JsonResponse
    {
        return response()->json($version->load(['package', 'files', 'builds']));
    }
}
