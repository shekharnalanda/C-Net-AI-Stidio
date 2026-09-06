<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkerDownloadController extends Controller
{
    public function download(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $path = public_path(
            'downloads/C-Net-AI-Worker-V5-Windows.zip'
        );

        abort_unless(is_file($path), 404);

        return response()->download(
            $path,
            'C-Net-AI-Worker-V5-Windows.zip',
            [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
