<?php

use App\Services\VideoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function fakeWelcomeVideoProcessor(float $duration = 15, int $outputBytes = 1024): void
{
    Process::fake(function ($process) use ($duration, $outputBytes) {
        $command = $process->command;
        if (in_array('-version', $command, true)) {
            return Process::result(output: 'processor available');
        }
        if (in_array('-show_entries', $command, true)) {
            return Process::result(output: json_encode([
                'format' => ['duration' => $duration],
                'streams' => [['codec_type' => 'video', 'width' => 1280, 'height' => 720]],
            ]));
        }
        file_put_contents(end($command), str_repeat('v', $outputBytes));

        return Process::result();
    });
    Process::preventStrayProcesses();
}

test('welcome videos are encoded as small silent portable mp4 files', function (int $outputBytes) {
    Storage::fake('public');
    fakeWelcomeVideoProcessor(outputBytes: $outputBytes);

    $path = app(VideoService::class)->uploadAndCompressVideo(
        UploadedFile::fake()->create('welcome.mp4', 10240, 'video/mp4')
    );

    expect($path)->toStartWith('welcome-videos/')->toEndWith('.mp4');
    Storage::disk('public')->assertExists($path);
    expect(Storage::disk('public')->size($path))->toBeLessThanOrEqual(VideoService::MAX_OUTPUT_BYTES);
    Process::assertRan(function ($process) {
        $command = $process->command;

        return in_array('libx264', $command, true)
            && in_array('-an', $command, true)
            && in_array('yuv420p', $command, true)
            && in_array('+faststart', $command, true)
            && in_array('24', $command, true);
    });
})->with([1024, VideoService::MAX_OUTPUT_BYTES]);

test('welcome videos longer than the exact duration limit are rejected before encoding', function () {
    Storage::fake('public');
    fakeWelcomeVideoProcessor(15.01);

    try {
        app(VideoService::class)->uploadAndCompressVideo(
            UploadedFile::fake()->create('welcome.mp4', 1024, 'video/mp4')
        );
        $this->fail('Expected duration validation to fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('welcome_video');
    }

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
    Process::assertNotRan(fn ($process) => in_array('-c:v', $process->command, true));
});

test('compressed output over the exact size limit is never stored', function () {
    Storage::fake('public');
    fakeWelcomeVideoProcessor(15, VideoService::MAX_OUTPUT_BYTES + 1);

    expect(fn () => app(VideoService::class)->uploadAndCompressVideo(
        UploadedFile::fake()->create('welcome.webm', 1024, 'video/webm')
    ))->toThrow(ValidationException::class);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('oversized original videos are rejected before encoding', function () {
    Storage::fake('public');
    fakeWelcomeVideoProcessor();

    expect(fn () => app(VideoService::class)->uploadAndCompressVideo(
        UploadedFile::fake()->create('welcome.mp4', VideoService::MAX_UPLOAD_KB + 1, 'video/mp4')
    ))->toThrow(ValidationException::class);

    Process::assertNotRan(fn ($process) => in_array('-c:v', $process->command, true));
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('missing video processors explicitly disable uploading', function () {
    Process::fake(fn () => Process::result(exitCode: 1));
    Storage::fake('public');
    $service = app(VideoService::class);

    expect($service->available())->toBeFalse();
    expect(fn () => $service->uploadAndCompressVideo(
        UploadedFile::fake()->create('welcome.mp4', 100, 'video/mp4')
    ))->toThrow(ValidationException::class);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('invalid video metadata is rejected instead of trusting the extension', function () {
    Process::fake(function ($process) {
        return in_array('-version', $process->command, true)
            ? Process::result()
            : Process::result(output: '{"streams":[],"format":{"duration":"NaN"}}');
    });
    Storage::fake('public');

    expect(fn () => app(VideoService::class)->uploadAndCompressVideo(
        UploadedFile::fake()->create('welcome.mp4', 100, 'video/mp4')
    ))->toThrow(ValidationException::class);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('encoder errors are reported without storing the original upload', function () {
    Storage::fake('public');
    Process::fake(function ($process) {
        if (in_array('-version', $process->command, true)) {
            return Process::result();
        }
        if (in_array('-show_entries', $process->command, true)) {
            return Process::result(output: json_encode([
                'format' => ['duration' => 5],
                'streams' => [['codec_type' => 'video', 'width' => 640, 'height' => 360]],
            ]));
        }

        return Process::result(errorOutput: 'Encoder unavailable', exitCode: 1);
    });

    try {
        app(VideoService::class)->uploadAndCompressVideo(
            UploadedFile::fake()->create('welcome.mp4', 1024, 'video/mp4')
        );
        $this->fail('Expected compression failure.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['welcome_video'][0])->toBe(__('media.processing_failed'));
    }

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('real video encoding meets browser playback and transfer requirements', function () {
    $service = app(VideoService::class);
    if (! $service->available()) {
        $this->markTestSkipped('Configure FFMPEG_BINARY and FFPROBE_BINARY to run real encoding coverage.');
    }

    Storage::fake('public');
    $source = tempnam(sys_get_temp_dir(), 'menu-source-');
    try {
        $generation = Process::timeout(90)->run([
            config('media.ffmpeg'), '-nostdin', '-y', '-v', 'error',
            '-f', 'lavfi', '-i', 'testsrc2=size=1280x720:rate=24',
            '-f', 'lavfi', '-i', 'sine=frequency=440',
            '-t', (string) VideoService::MAX_DURATION_SECONDS, '-c:v', 'libx264', '-preset', 'ultrafast',
            '-crf', '23', '-maxrate', '3000k', '-bufsize', '3000k',
            '-c:a', 'aac', '-threads', '2', '-f', 'mp4', $source,
        ]);
        expect($generation->successful())->toBeTrue($generation->errorOutput());
        $path = $service->uploadAndCompressVideo(new UploadedFile($source, 'welcome.mp4', 'video/mp4', null, true));
        $output = Storage::disk('public')->path($path);
        $probe = Process::timeout(10)->run([
            config('media.ffprobe'), '-v', 'error',
            '-show_entries', 'format=duration:stream=codec_name,codec_type,width,height,pix_fmt',
            '-of', 'json', $output,
        ]);
        $metadata = json_decode($probe->output(), true);

        expect($probe->successful())->toBeTrue()
            ->and($metadata['streams'])->toHaveCount(1)
            ->and($metadata['streams'][0]['codec_name'])->toBe('h264')
            ->and($metadata['streams'][0]['pix_fmt'])->toBe('yuv420p')
            ->and($metadata['streams'][0]['width'])->toBeLessThanOrEqual(1280)
            ->and($metadata['streams'][0]['height'])->toBeLessThanOrEqual(720)
            ->and((float) $metadata['format']['duration'])->toBeLessThanOrEqual(VideoService::MAX_DURATION_SECONDS)
            ->and(filesize($output))->toBeLessThanOrEqual(VideoService::MAX_OUTPUT_BYTES)
            ->and(filesize($output))->toBeLessThan(filesize($source));
    } finally {
        if (is_file($source)) {
            unlink($source);
        }
    }
});
