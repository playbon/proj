<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/register', [V1\AuthController::class, 'register']);
    Route::post('auth/login', [V1\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [V1\AuthController::class, 'logout']);
        Route::get('auth/me', [V1\AuthController::class, 'me']);

        Route::apiResource('packages', V1\PackageController::class);
        Route::get('packages/{package}/latest', [V1\PackageController::class, 'latest']);
        Route::apiResource('packages.versions', V1\VersionController::class)->shallow();
        Route::post('versions/{version}/files', [V1\FileController::class, 'store']);
        Route::delete('files/{file}', [V1\FileController::class, 'destroy']);
        Route::post('files/{file}/verify', [V1\FileController::class, 'verify']);
        Route::post('versions/{version}/build', [V1\BuildController::class, 'store']);
        Route::get('builds/{build}', [V1\BuildController::class, 'show']);
        Route::get('builds/{build}/download', [V1\BuildController::class, 'download']);
        Route::get('builds/{build}/manifest', [V1\BuildController::class, 'manifest']);
        Route::get('channels', [V1\ChannelController::class, 'index']);
        Route::post('channels', [V1\ChannelController::class, 'store']);
        Route::get('audit-logs', [V1\AuditController::class, 'index']);
        Route::get('audit-logs/export', [V1\AuditController::class, 'export']);
    });
});
