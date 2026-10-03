<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('uploaded images use compressed storage and bounded landscape and portrait dimensions', function (int $width, int $height) {
    Storage::fake('public');
    $path = app(ImageService::class)->uploadAndCompressImage(
        UploadedFile::fake()->image('menu.png', $width, $height), 'backgrounds', 800
    );

    Storage::disk('public')->assertExists($path);
    $dimensions = getimagesize(Storage::disk('public')->path($path));
    expect($dimensions[0])->toBeLessThanOrEqual(800)
        ->and($dimensions[1])->toBeLessThanOrEqual(800);
})->with([[1600, 900], [900, 1600]]);

test('image formats come from actual content rather than an untrusted filename', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('real.png', 32, 32);
    $renamed = new UploadedFile($image->getPathname(), 'fake.jpg', 'image/jpeg', null, true);
    $path = app(ImageService::class)->uploadAndCompressImage($renamed, 'logos');

    expect($path)->toEndWith('.png');
    expect(getimagesize(Storage::disk('public')->path($path))['mime'])->toBe('image/png');
});

test('invalid image content produces a validation error and no stored fallback', function () {
    Storage::fake('public');

    expect(fn () => app(ImageService::class)->uploadAndCompressImage(
        UploadedFile::fake()->create('invalid.png', 1, 'image/png'), 'backgrounds'
    ))->toThrow(ValidationException::class);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
