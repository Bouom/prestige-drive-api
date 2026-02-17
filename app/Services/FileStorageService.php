<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Upload a file and store metadata.
     *
     * @param  mixed  $owner  (User, Driver Profile, Company, Vehicle)
     * @param  string  $type  (avatar, document, vehicle_image, etc.)
     */
    public function uploadFile(
        UploadedFile $file,
        $owner,
        string $type = 'general',
        array $metadata = []
    ): Media {
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Determine storage path based on type
        $path = $this->getStoragePath($type);

        // Store file
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Create media record
        $media = Media::create([
            'mediable_type' => get_class($owner),
            'mediable_id' => $owner->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'disk' => 'public',
            'collection_name' => $type,
            'metadata' => array_merge($metadata, [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $extension,
            ]),
        ]);

        return $media;
    }

    /**
     * Upload a document with verification metadata.
     *
     * @param  mixed  $owner
     */
    public function uploadDocument(
        UploadedFile $file,
        $owner,
        int $documentTypeId,
        array $metadata = []
    ): Document {
        // Upload file
        $media = $this->uploadFile($file, $owner, 'documents', $metadata);

        // Create document record
        $document = Document::create([
            'documentable_type' => get_class($owner),
            'documentable_id' => $owner->id,
            'document_type_id' => $documentTypeId,
            'file_path' => $media->file_path,
            'file_name' => $media->file_name,
            'file_size' => $media->file_size,
            'mime_type' => $media->file_type,
            'document_number' => $metadata['document_number'] ?? null,
            'issued_at' => $metadata['issued_at'] ?? null,
            'expires_at' => $metadata['expires_at'] ?? null,
            'issuing_authority' => $metadata['issuing_authority'] ?? null,
        ]);

        return $document;
    }

    /**
     * Upload user avatar.
     *
     * @param  mixed  $user
     */
    public function uploadAvatar(UploadedFile $file, $user): Media
    {
        // Delete old avatar if exists
        $oldAvatar = Media::where('mediable_type', get_class($user))
            ->where('mediable_id', $user->id)
            ->where('collection_name', 'avatars')
            ->first();

        if ($oldAvatar) {
            $this->deleteFile($oldAvatar);
        }

        // Upload new avatar
        return $this->uploadFile($file, $user, 'avatars');
    }

    /**
     * Delete a media file.
     */
    public function deleteFile(Media $media): bool
    {
        // Delete file from storage
        if (Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }

        // Delete media record
        return $media->delete();
    }

    /**
     * Delete a document file.
     */
    public function deleteDocument(Document $document): bool
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete document record
        return $document->delete();
    }

    /**
     * Get the full URL for a media file.
     */
    public function getFileUrl(Media $media): string
    {
        return Storage::disk($media->disk)->url($media->file_path);
    }

    /**
     * Get the full URL for a document file.
     */
    public function getDocumentUrl(Document $document): string
    {
        return Storage::disk('public')->url($document->file_path);
    }

    /**
     * Get storage path based on type.
     */
    private function getStoragePath(string $type): string
    {
        $paths = [
            'avatars' => 'avatars',
            'documents' => 'documents',
            'vehicle_images' => 'vehicles',
            'company_logos' => 'companies',
            'banners' => 'banners',
            'news' => 'news',
            'general' => 'files',
        ];

        return $paths[$type] ?? 'files';
    }

    /**
     * Validate file type.
     */
    public function validateFileType(UploadedFile $file, array $allowedTypes): bool
    {
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        foreach ($allowedTypes as $allowed) {
            if (Str::contains($mimeType, $allowed) || $extension === $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate file size.
     */
    public function validateFileSize(UploadedFile $file, int $maxSizeKB): bool
    {
        return $file->getSize() <= ($maxSizeKB * 1024);
    }

    /**
     * Get file size in human readable format.
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }
}
