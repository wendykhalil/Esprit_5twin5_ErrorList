<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryImageService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new RuntimeException(
                'Cloudinary configuration is missing.'
            );
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    public function upload(UploadedFile $file): array
    {
        $result = $this->cloudinary
            ->uploadApi()
            ->upload($file->getRealPath(), [
                'folder' => 'solarshare/equipments',
                'resource_type' => 'image',
            ]);

        return [
            'secure_url' => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if (!$publicId) {
            return;
        }

        $this->cloudinary
            ->uploadApi()
            ->destroy($publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
    }
}