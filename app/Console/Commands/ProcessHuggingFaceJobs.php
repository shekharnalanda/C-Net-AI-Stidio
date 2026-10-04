<?php

namespace App\Console\Commands;

use App\Models\AIJob;
use App\Services\AI\HuggingFaceGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProcessHuggingFaceJobs extends Command
{
    protected $signature = 'studio:process-hf {--limit=2}';

    protected $description = 'Process pending voice and short image-to-video creations';

    public function handle(HuggingFaceGenerationService $service): int
    {
        $lock = Cache::store('file')->lock('studio-hf-processor', 1200);
        if (! $lock->get()) {
            return self::SUCCESS;
        }
        try {
            foreach (AIJob::where('engine', 'hf-gradio')->where('status', 'processing')
                ->where('started_at', '<', now()->subMinutes(15))->get() as $stale) {
                $stale->update(['status' => 'failed', 'progress' => 0, 'completed_at' => now(),
                    'error_message' => 'Creation was interrupted. Please submit a new request.']);
                $stale->project?->update(['status' => 'failed']);
                if (isset($stale->payload['image_path'])) {
                    Storage::disk('local')->delete($stale->payload['image_path']);
                }
            }
            $jobs = AIJob::where('engine', 'hf-gradio')->where('status', 'pending')->orderBy('id')
                ->limit(max(1, min(2, (int) $this->option('limit'))))->get();
            foreach ($jobs as $job) {
                $service->process($job);
                $this->info('Creation '.$job->id.': '.$job->fresh()->status);
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }
}
