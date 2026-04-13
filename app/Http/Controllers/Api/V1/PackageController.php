<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Services\DistributionService;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
        protected DistributionService $distributionService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Package::with(['user', 'channel']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->channel_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packageService->create($request->user(), $request->validated());

        return response()->json($package->load(['user', 'channel']), 201);
    }

    public function show(Package $package): JsonResponse
    {
        return response()->json($package->load(['user', 'channel', 'versions.files']));
    }

    public function update(UpdatePackageRequest $request, Package $package): JsonResponse
    {
        $package = $this->packageService->update($package, $request->validated());

        return response()->json($package->load(['user', 'channel']));
    }

    public function destroy(Package $package): JsonResponse
    {
        $this->packageService->delete($package);

        return response()->json(['message' => 'Package deleted successfully.']);
    }

    public function latest(Package $package): JsonResponse
    {
        $version = $this->distributionService->getLatestPublished($package);

        if (!$version) {
            return response()->json(['message' => 'No published version found.'], 404);
        }

        $build = $version->builds()
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$build) {
            return response()->json(['message' => 'No completed build found.'], 404);
        }

        $manifest = $build->artifacts()->where('type', 'manifest')->first();

        if ($manifest && file_exists(storage_path('app/' . $manifest->file_path))) {
            return response()->json(json_decode(file_get_contents(storage_path('app/' . $manifest->file_path)), true));
        }

        return response()->json([
            'package' => $package->name,
            'version' => $version->version,
            'channel' => $package->channel->name,
            'published_at' => $version->published_at,
        ]);
    }
}
