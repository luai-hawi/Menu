<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class ImageService
{
    public function uploadAndCompressImage(UploadedFile $file, string $directory, int $maxWidth = 800, int $quality = 80): string
    {
        $field = match ($directory) {
            'logos' => 'logo',
            'backgrounds' => 'background_image',
            default => 'image',
        };

        if (! extension_loaded('gd')) {
            Log::error('Image processing requires the PHP GD extension.');
            throw ValidationException::withMessages([$field => __('media.image_processor_unavailable')]);
        }

        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };

        $dimensions = @getimagesize($file->getPathname());
        if ($extension === null || $dimensions === false || $dimensions[0] * $dimensions[1] > 40000000) {
            throw ValidationException::withMessages([$field => __('media.invalid_image')]);
        }

        try {
            $image = ImageManager::gd(decodeAnimation: false)->read($file->getPathname());
        } catch (DecoderException $exception) {
            Log::warning('An uploaded image could not be decoded.', ['exception' => $exception]);
            throw ValidationException::withMessages([$field => __('media.invalid_image')]);
        }

        $image->scaleDown(width: $maxWidth, height: $maxWidth);
        $encoded = $image->encodeByExtension($extension, quality: $quality);
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($path, (string) $encoded)) {
            throw new \RuntimeException('Unable to store the processed image.');
        }

        return $path;
    }

    public function deleteImage(?string $imagePath): bool
    {
        if (! $imagePath || ! Storage::disk('public')->exists($imagePath)) {
            return false;
        }

        if (! Storage::disk('public')->delete($imagePath)) {
            throw new \RuntimeException('Unable to delete the stored image.');
        }

        return true;
    }
}
