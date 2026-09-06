<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIJob;
use App\Models\AIWorker;
use App\Models\StudioProject;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('studio.dashboard', [
            'user' => $user,
            'projects' => StudioProject::where('user_id', $user->id)
                ->latest()
                ->limit(8)
                ->get(),
            'projectCount' => StudioProject::where('user_id', $user->id)->count(),
            'jobCount' => AIJob::where('user_id', $user->id)->count(),
            'onlineWorkers' => AIWorker::where('status', 'online')->count(),
            'plans' => SubscriptionPlan::where('is_active', true)
                ->orderBy('price')
                ->get(),
        ]);
    }
}
