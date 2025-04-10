<?php

namespace App\Http\Controllers;

use App\Http\Requests\Image\UploadImageRequest;
use App\Models\Image;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    public function uploadImage(UploadImageRequest $request)
    {
        try {
            $files = $request->file('images');

            if (!$files || count($files) === 0) {
                return $this->badRequestResponse(400, 'No valid images uploaded.');
            }

            DB::beginTransaction();

            $uploadedImages = [];

            foreach ($request->file('images') as $imageFile) {
                $folder = $this->getFolder($request);
    
                $uploaded = Cloudinary::uploadApi()->upload($imageFile->getRealPath(), [
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'fetch_format' => 'auto',
                    'quality' => 'auto',
                ]);
    
                $uploadedImages[] = Image::create([
                    'id_image' => Str::uuid(),
                    'property_id' => $request->property_id,
                    'room_id' => $request->room_id,
                    'reservation_id' => $request->reservation_id,
                    'img_url' => $uploaded['secure_url'],
                ]);
            }

            DB::commit();

            return $this->successResponse(200, $uploadedImages, 'Images uploaded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload images: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to upload images');
        }
    }

    public function destroyImage(string $id)
    {
        try {
            DB::beginTransaction();

            $image = Image::findOrFail($id);
            $publicId = $this->extractPublicId($image->img_url);
            $folder = $this->getFolderFromImage($image);

            Cloudinary::uploadApi()->destroy($folder . '/' . $publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);

            $image->delete();

            DB::commit();

            return $this->successResponse(200, null, 'Image deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete image: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete image');
        }
    }

    private function getFolder($request)
    {
        if ($request->room_id) return 'room_images';
        if ($request->property_id) return 'property_images';
        if ($request->reservation_id) return 'reservation_images';
        return 'misc';
    }

    private function getFolderFromImage($image)
    {
        if ($image->property_id) {
            return 'property_images';
        } elseif ($image->room_id) {
            return 'room_images';
        } elseif ($image->reservation_id) {
            return 'reservation_images';
        }
        return 'misc_images';
    }

    private function extractPublicId($url)
    {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $filename = end($parts);
        return pathinfo($filename, PATHINFO_FILENAME);
    }
}
