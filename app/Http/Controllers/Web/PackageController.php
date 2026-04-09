<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\StoreVersionRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\UploadFilesRequest;
use App\Models\Channel;
use App\Models\Package;
use App\Models\PackageFile;
use App\Models\PackageVersion;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(protected PackageService $packageService)
    {
    }

    public function index(Request $request): View
    {
        $query = Package::with(['user', 'channel', 'versions']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('channel')) {
            $query->where('channel_id', $request->channel);
        }

        $packages = $query->latest()->paginate(15);
        $channels = Channel::all();

        return view('packages.index', compact('packages', 'channels'));
    }

    public function create(): View
    {
        $this->authorize('create', Package::class);
        $channels = Channel::all();
        return view('packages.create', compact('channels'));
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $package = $this->packageService->create($request->user(), $request->validated());

        return redirect()->route('packages.show', $package)->with('success', 'Package created successfully.');
    }

    public function show(Package $package): View
    {
        $package->load(['user', 'channel', 'versions.files', 'versions.builds']);

        return view('packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        $this->authorize('update', $package);
        $channels = Channel::all();
        return view('packages.edit', compact('package', 'channels'));
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('update', $package);
        $this->packageService->update($package, $request->validated());

        return redirect()->route('packages.show', $package)->with('success', 'Пакет обновлён.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->authorize('delete', $package);
        $this->packageService->delete($package);

        return redirect()->route('packages.index')->with('success', 'Пакет удалён.');
    }

    public function storeVersion(StoreVersionRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('update', $package);
        $this->packageService->createVersion($package, $request->validated());

        return redirect()->route('packages.show', $package)->with('success', 'Версия добавлена.');
    }

    public function uploadFiles(UploadFilesRequest $request, PackageVersion $version): RedirectResponse
    {
        $this->authorize('update', $version->package);
        $this->packageService->uploadFiles($version, $request->file('files'));

        return redirect()->route('packages.show', $version->package)->with('success', 'Файлы загружены.');
    }

    public function deleteFile(PackageFile $file): RedirectResponse
    {
        $package = $file->packageVersion->package;
        $this->authorize('update', $package);
        \Illuminate\Support\Facades\Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return redirect()->route('packages.show', $package)->with('success', 'Файл удалён.');
    }
}
