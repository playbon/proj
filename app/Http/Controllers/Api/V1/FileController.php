<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFilesRequest;
use App\Models\PackageFile;
use App\Models\PackageVersion;
use App\Services\PackageService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
        protected VerificationService $verificationService
    ) {
    }

    public function store(UploadFilesRequest $request, PackageVersion $version): JsonResponse
    {
        $files = $this->packageService->uploadFiles($version, $request->file('files'));

        return response()->json($files, 201);
    }

    public function destroy(PackageFile $file): JsonResponse
    {
        Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully.']);
    }

    public function verify(PackageFile $file): JsonResponse
    {
        $isValid = $this->verificationService->verifyFile($file);

        return response()->json([
            'file_id' => $file->id,
            'valid' => $isValid,
            'status' => $file->fresh()->verification_status,
        ]);
    }
}
