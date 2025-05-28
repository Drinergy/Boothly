<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoboothController extends Controller
{
    public function index()
    {
        return view('photobooth.index');
    }

    public function capture(Request $request)
    {
        try {
            // Log the incoming request for debugging
            \Log::info('Photo capture request received', [
                'session_id' => $request->input('session_id'),
                'photo_count' => $request->input('photo_count'),
                'image_length' => strlen($request->input('image', '')),
                'image_start' => substr($request->input('image', ''), 0, 50)
            ]);

            // Simplified validation - remove problematic regex
            $request->validate([
                'image' => 'required|string|min:100',
                'photo_count' => 'required|integer|min:1|max:6',
                'session_id' => 'required|string|min:1|max:50'
            ]);

            // Get and clean the base64 image data
            $imageData = $request->input('image');
            
            // Handle different image formats (PNG, JPEG, WebP)
            $imageData = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = str_replace(["\r", "\n"], '', $imageData);
            
            // More lenient base64 validation - just check if it's mostly base64 characters
            if (!preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $imageData)) {
                \Log::error('Invalid base64 format', ['data_start' => substr($imageData, 0, 100)]);
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid image format'
                ], 400);
            }
            
            // Decode base64 image
            $decodedImage = base64_decode($imageData, true);
            
            if ($decodedImage === false) {
                \Log::error('Base64 decode failed');
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to decode image data'
                ], 400);
            }
            
            // Validate image size (max 10MB)
            if (strlen($decodedImage) > 10 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'error' => 'Image too large'
                ], 400);
            }

            // Generate unique filename with safe characters - clean session ID
            $sessionId = preg_replace('/[^a-zA-Z0-9]/', '', $request->session_id);
            if (empty($sessionId)) {
                $sessionId = 'session_' . time();
            }
            $filename = 'photo_' . $sessionId . '_' . time() . '_' . Str::random(8) . '.png';
            
            // Ensure photos directory exists
            $photosDir = storage_path('app/public/photos');
            if (!file_exists($photosDir)) {
                mkdir($photosDir, 0755, true);
            }
            
            // Store the image
            $stored = Storage::disk('public')->put('photos/' . $filename, $decodedImage);
            
            if (!$stored) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save image'
                ], 500);
            }

            // Verify file was created
            $filePath = storage_path('app/public/photos/' . $filename);
            $fileExists = file_exists($filePath);
            
            \Log::info('Photo captured successfully', [
                'filename' => $filename,
                'file_path' => $filePath,
                'file_exists' => $fileExists,
                'file_size' => $fileExists ? filesize($filePath) : 0
            ]);

            // Generate the correct URL for XAMPP
            $baseUrl = $request->getSchemeAndHttpHost();
            $photoUrl = $baseUrl . '/photobooth/public/storage/photos/' . $filename;

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'path' => $photoUrl
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error', ['errors' => $e->validator->errors()->all()]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Photo capture error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Server error occurred while processing image'
            ], 500);
        }
    }

    public function createCollage(Request $request)
    {
        $request->validate([
            'photos' => 'required|array|min:1|max:6',
            'session_id' => 'required|string'
        ]);

        $photos = $request->input('photos');
        $sessionId = $request->input('session_id');

        // Create Instax-style collage
        $collageFilename = $this->generateInstaxCollage($photos, $sessionId);

        return response()->json([
            'success' => true,
            'collage_url' => Storage::url('collages/' . $collageFilename),
            'download_url' => route('photobooth.download', ['filename' => $collageFilename])
        ]);
    }

    public function download($filename)
    {
        $path = storage_path('app/public/collages/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'photobooth_collage_' . date('Y-m-d_H-i-s') . '.png');
    }

    private function generateInstaxCollage($photos, $sessionId)
    {
        $photoCount = count($photos);
        
        // Instax photo dimensions
        $photoWidth = 320;
        $photoHeight = 400;
        $borderWidth = 20;
        $bottomBorder = 80; // Extra space at bottom for Instax look
        $spacing = 40; // Space between photos
        $headerSpace = 100; // Space for title
        $footerSpace = 60; // Space for date
        
        // Calculate layout based on photo count
        if ($photoCount == 1) {
            $cols = 1;
            $rows = 1;
        } elseif ($photoCount == 2) {
            $cols = 2;
            $rows = 1;
        } elseif ($photoCount <= 4) {
            $cols = 2;
            $rows = 2;
        } else {
            $cols = 3;
            $rows = 2;
        }
        
        // Calculate frame dimensions
        $frameWidth = $photoWidth + ($borderWidth * 2);
        $frameHeight = $photoHeight + $borderWidth + $bottomBorder;
        
        // Calculate dynamic canvas size
        $canvasWidth = ($cols * $frameWidth) + (($cols - 1) * $spacing) + (2 * $spacing); // Add padding on sides
        $canvasHeight = ($rows * $frameHeight) + (($rows - 1) * $spacing) + $headerSpace + $footerSpace;
        
        // Minimum canvas size for single photo
        if ($photoCount == 1) {
            $canvasWidth = max($canvasWidth, 500); // Minimum width
            $canvasHeight = max($canvasHeight, 600); // Minimum height
        }
        
        // Create main canvas with white background
        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // Calculate starting position to center the photos
        $totalPhotosWidth = ($cols * $frameWidth) + (($cols - 1) * $spacing);
        $startX = ($canvasWidth - $totalPhotosWidth) / 2;
        $startY = $headerSpace;

        foreach ($photos as $index => $photoFilename) {
            if ($index >= $photoCount) break; // Safety check
            
            $photoPath = storage_path('app/public/photos/' . $photoFilename);
            
            if (file_exists($photoPath)) {
                $photo = imagecreatefrompng($photoPath);
                
                // Calculate position
                $col = $index % $cols;
                $row = floor($index / $cols);
                
                $x = $startX + $col * ($frameWidth + $spacing);
                $y = $startY + $row * ($frameHeight + $spacing);

                // Draw subtle shadow first
                $shadowColor = imagecolorallocate($canvas, 220, 220, 220);
                imagefilledrectangle($canvas, $x + 5, $y + 5, $x + $frameWidth + 5, $y + $frameHeight + 5, $shadowColor);
                
                // Create Instax frame
                $frameColor = imagecolorallocate($canvas, 248, 248, 248);
                imagefilledrectangle($canvas, $x, $y, $x + $frameWidth, $y + $frameHeight, $frameColor);

                // Resize and place photo
                $resizedPhoto = imagecreatetruecolor($photoWidth, $photoHeight);
                
                // Get original photo dimensions
                $originalWidth = imagesx($photo);
                $originalHeight = imagesy($photo);
                $originalAspectRatio = $originalWidth / $originalHeight;
                $targetAspectRatio = $photoWidth / $photoHeight;
                
                // Calculate crop dimensions to maintain aspect ratio
                if ($originalAspectRatio > $targetAspectRatio) {
                    // Original is wider, crop width
                    $cropHeight = $originalHeight;
                    $cropWidth = $cropHeight * $targetAspectRatio;
                    $cropX = ($originalWidth - $cropWidth) / 2;
                    $cropY = 0;
                } else {
                    // Original is taller, crop height
                    $cropWidth = $originalWidth;
                    $cropHeight = $cropWidth / $targetAspectRatio;
                    $cropX = 0;
                    $cropY = ($originalHeight - $cropHeight) / 2;
                }
                
                // Copy and resize with proper cropping
                imagecopyresampled(
                    $resizedPhoto, $photo, 
                    0, 0, 
                    $cropX, $cropY, 
                    $photoWidth, $photoHeight, 
                    $cropWidth, $cropHeight
                );
                
                imagecopy($canvas, $resizedPhoto, $x + $borderWidth, $y + $borderWidth, 0, 0, $photoWidth, $photoHeight);

                // Add photo number in bottom border
                $textColor = imagecolorallocate($canvas, 100, 100, 100);
                $font = 3;
                $text = sprintf("%02d", $index + 1);
                $textX = $x + $frameWidth - 30;
                $textY = $y + $photoHeight + $borderWidth + 20;
                imagestring($canvas, $font, $textX, $textY, $text, $textColor);

                imagedestroy($photo);
                imagedestroy($resizedPhoto);
            }
        }

        // Add title
        $titleColor = imagecolorallocate($canvas, 80, 80, 80);
        $title = "PHOTOBOOTH MEMORIES";
        $titleX = ($canvasWidth - strlen($title) * 12) / 2;
        imagestring($canvas, 5, $titleX, 30, $title, $titleColor);

        // Add date
        $date = date('Y.m.d H:i');
        $dateX = ($canvasWidth - strlen($date) * 10) / 2;
        imagestring($canvas, 4, $dateX, $canvasHeight - 40, $date, $titleColor);
        
        // Add photo count info
        $countText = $photoCount . " photo" . ($photoCount > 1 ? "s" : "");
        $countX = ($canvasWidth - strlen($countText) * 8) / 2;
        imagestring($canvas, 3, $countX, $canvasHeight - 20, $countText, $titleColor);

        // Save collage
        $collageFilename = 'collage_' . $sessionId . '_' . time() . '.png';
        $collagePath = storage_path('app/public/collages/' . $collageFilename);
        
        // Create collages directory if it doesn't exist
        if (!file_exists(dirname($collagePath))) {
            mkdir(dirname($collagePath), 0755, true);
        }

        imagepng($canvas, $collagePath);
        imagedestroy($canvas);

        return $collageFilename;
    }
}
