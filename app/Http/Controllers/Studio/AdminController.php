<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Models\AIWorker;
use App\Models\StudioProject;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('studio.admin', [
            'users' => User::latest()->limit(20)->get(),
            'userCount' => User::count(),
            'projectCount' => StudioProject::count(),
            'jobCount' => AIJob::count(),
            'workerCount' => AIWorker::count(),
            'onlineWorkerCount' => AIWorker::where('status', 'online')->count(),
        ]);
    }
}
