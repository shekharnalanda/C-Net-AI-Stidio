<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\StudioMedia;
use App\Models\StudioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(Request $request, StudioProject $project)
    {
        abort_unless(
            $project->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $request->validate([
            'media' => [
                'required',
                'file',
                'max:102400',
                'mimes:jpg,jpeg,png,webp,mp4,mov,webm,mp3,wav,m4a',
            ],
        ]);

        $file = $request->file('media');

        $path = $file->store(
            'studio/'.$request->user()->id.'/'.$project->id,
            'public'
        );

        $mime = $file->getMimeType() ?: 'application/octet-stream';

        $type = str_starts_with($mime, 'image/')
            ? 'image'
            : (
                str_starts_with($mime, 'video/')
                    ? 'video'
                    : (str_starts_with($mime, 'audio/') ? 'audio' : 'file')
            );

        StudioMedia::create([
            'user_id' => $request->user()->id,
            'project_id' => $project->id,
            'media_type' => $type,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);

        return back()->with('success', 'Media uploaded successfully.');
    }

    public function destroy(Request $request, StudioMedia $media)
    {
        abort_unless(
            $media->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('success', 'Media removed.');
    }
}
