<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use Illuminate\Http\Request;

class AIJobController extends Controller
{
    public function status(Request $request, AIJob $job)
    {
        abort_unless(
            $job->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        return response()->json([
            'id' => $job->id,
            'uuid' => $job->job_uuid,
            'status' => $job->status,
            'progress' => $job->progress,
            'engine' => $job->engine,
            'result' => $job->result,
            'error' => $job->error_message,
        ]);
    }
}
