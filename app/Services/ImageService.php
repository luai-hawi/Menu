<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function uploadAndCompressImage(UploadedFile $file, string $directory, int $maxWidth = 800, int $quality = 80): string
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;
        
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            // Fallback to simple upload without compression
            Storage::disk('public')->putFileAs($directory, $file, $filename);
            return $path;
        }
        
        $originalPath = $file->getPathname();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Create image resource based on file type
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($originalPath);
                break;
            case 'png':
                $image = imagecreatefrompng($originalPath);
                break;
            case 'gif':
                $image = imagecreatefromgif($originalPath);
                break;
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = imagecreatefromwebp($originalPath);
                }
                break;
        }
        
        if (!$image) {
            // If we can't process the image, just upload it as-is
            Storage::disk('public')->putFileAs($directory, $file, $filename);
            return $path;
        }
        
        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // Calculate new dimensions
        if ($originalWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval(($originalHeight * $maxWidth) / $originalWidth);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        // Create new resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNG and GIF
        if ($extension === 'png' || $extension === 'gif') {
            imagecolortransparent($resizedImage, imagecolorallocate($resizedImage, 0, 0, 0));
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }
        
        // Resize the image
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Create storage directory if it doesn't exist
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        $fullPath = storage_path('app/public/' . $path);
        
        // Save the image based on type
        $success = false;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($resizedImage, $fullPath, $quality);
                break;
            case 'png':
                // Convert quality from 0-100 to 0-9 for PNG
                $pngQuality = intval((100 - $quality) / 10);
                $success = imagepng($resizedImage, $fullPath, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($resizedImage, $fullPath);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $success = imagewebp($resizedImage, $fullPath, $quality);
                }
                break;
        }
        
        // Clean up memory
        imagedestroy($image);
        imagedestroy($resizedImage);
        
        if (!$success) {
            // If saving failed, try simple upload
            Storage::disk('public')->putFileAs($directory, $file, $filename);
        }
        
        return $path;
    }
    
    public function deleteImage(?string $imagePath): bool
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->delete($imagePath);
        }
        
        return false;
    }
}