<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Services\Workers\WorkerProtocolService;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function register(
        Request $request,
        WorkerProtocolService $service
    ) {
        return response()->json(
            $service->register($request->all())
        );
    }

    public function nextJob(
        Request $request,
        WorkerProtocolService $service
    ) {
        $request->validate([
            'worker_uuid' => ['required', 'string'],
        ]);

        return response()->json([
            'job' => $service->nextJob(
                $request->string('worker_uuid')->toString()
            ),
        ]);
    }

    public function complete(Request $request, AIJob $job)
    {
        $job->update([
            'status' => 'completed',
            'progress' => 100,
            'result' => $request->input('result', []),
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
