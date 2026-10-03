<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VideoService
{
    public const MAX_UPLOAD_KB = 10240;

    public const MAX_DURATION_SECONDS = 15;

    public const MAX_OUTPUT_BYTES = 2097152;

    private ?bool $processorsAvailable = null;

    public function available(): bool
    {
        if ($this->processorsAvailable === null) {
            try {
                $this->processorsAvailable = Process::timeout(5)
                    ->run([config('media.ffmpeg'), '-version'])->successful()
                    && Process::timeout(5)->run([config('media.ffprobe'), '-version'])->successful();
            } catch (ProcessTimedOutException $exception) {
                Log::warning('Welcome video processor check timed out.', ['exception' => $exception]);
                $this->processorsAvailable = false;
            }
        }

        return $this->processorsAvailable;
    }

    public function uploadAndCompressVideo(UploadedFile $file): string
    {
        if (! $this->available()) {
            throw ValidationException::withMessages(['welcome_video' => __('media.unavailable')]);
        }

        if (! $file->isValid() || $file->getSize() > self::MAX_UPLOAD_KB * 1024) {
            throw ValidationException::withMessages(['welcome_video' => __('media.upload_size')]);
        }

        $temporary = tempnam(sys_get_temp_dir(), 'menu-video-');
        if ($temporary === false) {
            throw new \RuntimeException('Unable to allocate video processing storage.');
        }

        try {
            $this->inspectVideo($file->getPathname());
            $result = Process::timeout(90)->run([
                config('media.ffmpeg'), '-nostdin', '-y', '-v', 'error',
                '-protocol_whitelist', 'file,pipe',
                '-i', $file->getPathname(), '-map', '0:v:0', '-an', '-t', (string) self::MAX_DURATION_SECONDS,
                '-map_metadata', '-1', '-map_chapters', '-1',
                '-vf', 'scale=w=min(1280\,iw):h=min(720\,ih):force_original_aspect_ratio=decrease:force_divisible_by=2',
                '-r', '24', '-c:v', 'libx264', '-preset', 'fast',
                '-crf', '28', '-maxrate', '800k', '-bufsize', '1600k',
                '-pix_fmt', 'yuv420p', '-movflags', '+faststart',
                '-threads', '2', '-f', 'mp4', $temporary,
            ]);

            if (! $result->successful()) {
                Log::error('Welcome video compression failed.', ['error' => $result->errorOutput()]);
                throw ValidationException::withMessages(['welcome_video' => __('media.processing_failed')]);
            }

            clearstatcache(true, $temporary);
            $size = filesize($temporary);
            if ($size === false || $size === 0 || $size > self::MAX_OUTPUT_BYTES) {
                throw ValidationException::withMessages(['welcome_video' => __('media.output_size')]);
            }

            $this->inspectVideo($temporary);
            $path = 'welcome-videos/'.Str::uuid().'.mp4';
            $stream = fopen($temporary, 'rb');
            if ($stream === false) {
                throw new \RuntimeException('Unable to read compressed welcome video.');
            }

            try {
                if (! Storage::disk('public')->put($path, $stream)) {
                    throw new \RuntimeException('Unable to store compressed welcome video.');
                }
            } finally {
                fclose($stream);
            }

            return $path;
        } catch (ProcessTimedOutException $exception) {
            Log::warning('Welcome video processing timed out.', ['exception' => $exception]);
            throw ValidationException::withMessages(['welcome_video' => __('media.processing_failed')]);
        } finally {
            if (is_file($temporary) && ! unlink($temporary)) {
                Log::warning('Unable to remove temporary welcome video.', ['path' => $temporary]);
            }
        }
    }

    private function inspectVideo(string $path): void
    {
        $result = Process::timeout(10)->run([
            config('media.ffprobe'), '-v', 'error', '-protocol_whitelist', 'file,pipe', '-select_streams', 'v:0',
            '-show_entries', 'format=duration:stream=codec_type,width,height,duration',
            '-of', 'json', $path,
        ]);
        $metadata = json_decode($result->output(), true);
        $stream = is_array($metadata) ? ($metadata['streams'][0] ?? null) : null;
        $duration = $metadata['format']['duration'] ?? $stream['duration'] ?? null;

        if (! $result->successful() || ! is_array($stream) || ! is_numeric($duration)
            || (float) $duration <= 0 || ($stream['codec_type'] ?? '') !== 'video'
            || ($stream['width'] ?? 0) < 2 || ($stream['height'] ?? 0) < 2
            || ($stream['width'] ?? 0) > 4096 || ($stream['height'] ?? 0) > 4096) {
            throw ValidationException::withMessages(['welcome_video' => __('media.invalid_video')]);
        }

        if ((float) $duration > self::MAX_DURATION_SECONDS) {
            throw ValidationException::withMessages(['welcome_video' => __('media.duration')]);
        }
    }
}
