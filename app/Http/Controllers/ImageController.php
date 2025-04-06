<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    use ApiResponse;

    public function uploadProfilePicture($newImage, $oldImageUrl = null)
    {
        try {
            if (!$newImage || !$newImage->isValid()) {
                Log::error('Invalid uploaded image.');
                return null;
            }

            $folder = 'profile_picture';
            
            if ($oldImageUrl) {
                $publicId = $this->extractPublicId($oldImageUrl);
                Cloudinary::uploadApi()->destroy($folder . '/' . $publicId);
            }

            $uploadedFile = Cloudinary::uploadApi()->upload($newImage->getRealPath(), [
                'folder' => $folder,
                'fetch_format' => 'auto',
                'quality' => 'auto',
                'transformation' => [
                    'aspect_ratio' => "1.0",
                    'crop' => "fill"
                ]
            ]);

            $imageUrl = $uploadedFile['secure_url'];
            return $imageUrl;

        } catch (\Exception $e) {
            Log::error('Failed to upload profile picture: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to upload profile picture');
        }
    }

    private function extractPublicId($url)
    {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $filename = end($parts);
        return pathinfo($filename, PATHINFO_FILENAME);
    }
}
