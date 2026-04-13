<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChannelRequest;
use App\Models\Channel;
use Illuminate\Http\JsonResponse;

class ChannelController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Channel::all());
    }

    public function store(StoreChannelRequest $request): JsonResponse
    {
        $channel = Channel::create($request->validated());

        return response()->json($channel, 201);
    }
}
