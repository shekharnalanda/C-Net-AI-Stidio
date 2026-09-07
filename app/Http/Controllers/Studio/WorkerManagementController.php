<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\AIWorker;
use App\Models\WorkerActivationCode;
use App\Services\Workers\WorkerCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerManagementController extends Controller
{
    protected function admin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }

    public function index(Request $request)
    {
        $this->admin($request);

        return view('studio.workers', [
            'workers' => AIWorker::latest('id')->get(),
            'activations' => WorkerActivationCode::latest('id')
                ->limit(20)
                ->get(),
        ]);
    }

    public function createActivation(Request $request)
    {
        $this->admin($request);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:150'],
        ]);

        $plain = strtoupper(Str::random(5).'-'.Str::random(5));

        WorkerActivationCode::create([
            'code_hash' => Hash::make($plain),
            'label' => $data['label'] ?? 'New Worker',
            'created_by' => $request->user()->id,
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with(
            'activation_code',
            $plain
        )->with(
            'success',
            'One-time activation code created. It expires in 24 hours.'
        );
    }

    public function toggle(Request $request, AIWorker $worker)
    {
        $this->admin($request);

        $worker->update([
            'is_enabled' => ! $worker->is_enabled,
            'status' => $worker->is_enabled ? 'disabled' : 'offline',
        ]);

        return back()->with(
            'success',
            'Worker access status updated.'
        );
    }

    public function rotate(
        Request $request,
        AIWorker $worker,
        WorkerCredentialService $credentials
    ) {
        $this->admin($request);

        $token = $credentials->issueToken($worker);

        return back()
            ->with('worker_new_token', $token)
            ->with('worker_token_id', $worker->id)
            ->with(
                'success',
                'Worker credential reset successfully. Update this worker with the new token.'
            );
    }

    public function destroy(Request $request, AIWorker $worker)
    {
        $this->admin($request);

        abort_if($worker->current_job_id, 409, 'Worker currently has an active job.');

        $worker->delete();

        return back()->with('success', 'Worker removed.');
    }
}
