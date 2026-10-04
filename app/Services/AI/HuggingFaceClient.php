<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class HuggingFaceClient
{
    public const TTS_HOST = 'https://innoai-edge-tts-text-to-speech.hf.space';

    public const VIDEO_HOST = 'https://zerogpu-aoti-wan2-2-fp8da-aoti-faster.hf.space';

    private function http(): PendingRequest
    {
        $request = Http::connectTimeout(15)->timeout(40)->withOptions(['allow_redirects' => false]);
        $token = config('huggingface.token');

        return $token ? $request->withToken($token) : $request;
    }

    public function generate(array $payload, string $uuid): array
    {
        $video = $payload['kind'] === 'image-to-video';
        $host = $video ? self::VIDEO_HOST : self::TTS_HOST;
        $endpoint = $video ? 'generate_video' : 'tts_interface';

        if ($video) {
            $path = $payload['image_path'];
            $stream = Storage::disk('local')->readStream($path);
            if (! is_resource($stream)) {
                throw new RuntimeException('Input image is unavailable.');
            }
            try {
                $uploaded = $this->http()->attach('files', $stream, basename($path))
                    ->post($host.'/gradio_api/upload')->throw()->json();
            } finally {
                fclose($stream);
            }
            if (! is_string($uploaded[0] ?? null)) {
                throw new RuntimeException('Image upload did not complete.');
            }
            $data = [
                ['path' => $uploaded[0], 'orig_name' => basename($path), 'meta' => ['_type' => 'gradio.FileData']],
                $payload['prompt'], 6, 'distorted, low quality, text',
                (float) $payload['duration'], 1.0, 1.0, 42, false,
            ];
        } else {
            $data = [$payload['text'], config('huggingface.voices.'.$payload['voice']), (int) $payload['rate'], 0];
        }

        $eventId = $this->http()->post($host.'/gradio_api/call/'.$endpoint, ['data' => $data])
            ->throw()->json('event_id');
        if (! is_string($eventId) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $eventId)) {
            throw new RuntimeException('Generation request was not accepted.');
        }
        $response = $this->http()->timeout(210)->withOptions(['stream' => true, 'read_timeout' => 45])
            ->get($host.'/gradio_api/call/'.$endpoint.'/'.$eventId)->throw();
        $body = $response->toPsrResponse()->getBody();
        $buffer = '';
        $event = '';
        $result = null;
        $deadline = microtime(true) + 210;
        try {
            while (! $body->eof() && microtime(true) < $deadline) {
                $buffer .= $body->read(4096);
                while (($newline = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $newline));
                    $buffer = substr($buffer, $newline + 1);
                    if (str_starts_with($line, 'event:')) {
                        $event = trim(substr($line, 6));
                    } elseif (str_starts_with($line, 'data:')) {
                        if ($event === 'error') {
                            throw new RuntimeException('The generation service could not finish this request. Please try again later.');
                        }
                        if ($event === 'complete') {
                            $result = json_decode(trim(substr($line, 5)), true, 512, JSON_THROW_ON_ERROR);
                            break 2;
                        }
                    }
                }
                if (strlen($buffer) > 1048576) {
                    throw new RuntimeException('Unexpected generation response.');
                }
            }
        } finally {
            $body->close();
        }
        if (! is_array($result[0] ?? null)) {
            throw new RuntimeException('Generation timed out or ended without a file. Please try again later.');
        }
        $file = $video ? ($result[0]['video'] ?? $result[0]) : $result[0];
        $url = $file['url'] ?? $host.'/gradio_api/file='.($file['path'] ?? '');
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https'
            || ($parts['host'] ?? '') !== parse_url($host, PHP_URL_HOST)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])
            || ! str_starts_with($parts['path'] ?? '', '/gradio_api/file=')) {
            throw new RuntimeException('The service returned an unexpected file address.');
        }

        $extension = $video ? 'mp4' : 'mp3';
        $relative = 'hf-outputs/'.$uuid.'.'.$extension;
        $disk = Storage::disk('local');
        $disk->makeDirectory('hf-outputs');
        $disk->put('hf-outputs/.htaccess', "Require all denied\n");
        $download = $this->http()->timeout(90)->withOptions(['stream' => true])
            ->get($url)->throw()->toPsrResponse()->getBody();
        $output = fopen($disk->path($relative), 'wb');
        if (! is_resource($output)) {
            $download->close();
            throw new RuntimeException('Could not save the generated file.');
        }
        $size = 0;
        try {
            while (! $download->eof()) {
                $chunk = $download->read(65536);
                $size += strlen($chunk);
                if ($size > 100 * 1024 * 1024) {
                    throw new RuntimeException('Generated file exceeds the download limit.');
                }
                if (fwrite($output, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('Could not save the complete file.');
                }
            }
        } catch (\Throwable $exception) {
            fclose($output);
            $disk->delete($relative);
            throw $exception;
        } finally {
            $download->close();
        }
        fclose($output);
        $header = file_get_contents($disk->path($relative), false, null, 0, 16);
        $valid = $video ? substr($header, 4, 4) === 'ftyp'
            : (str_starts_with($header, 'ID3') || (strlen($header) > 1 && ord($header[0]) === 255 && (ord($header[1]) & 224) === 224));
        if ($size < 32 || ! $valid) {
            $disk->delete($relative);
            throw new RuntimeException('The service returned an invalid media file.');
        }

        return ['output_path' => $disk->path($relative), 'size_bytes' => $size, 'engine' => 'hf-gradio'];
    }
}
