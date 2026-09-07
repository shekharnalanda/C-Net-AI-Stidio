<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Studio\UsageService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request, UsageService $usage)
    {
        return view('studio.billing', [
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('price')->get(),
            'subscription' => $request->user()->subscriptions()->with('plan')->latest()->first(),
            'usage' => $usage->summary($request->user()),
        ]);
    }
}
