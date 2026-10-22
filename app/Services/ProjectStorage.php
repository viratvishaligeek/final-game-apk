<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProjectStorage
{
    public function publicDiskName(): string
    {
        return (string) config('project-storage.public_disk', 'public');
    }

    public function privateDiskName(): string
    {
        return (string) config('project-storage.private_disk', 'local');
    }

    public function storePublicFile(UploadedFile $file, string $directory): string
    {
        $path = Storage::disk($this->publicDiskName())->putFile($directory, $file);

        if (!is_string($path) || $path === '') {
            throw new RuntimeException('Unable to store the uploaded public file.');
        }

        return $path;
    }

    public function storePrivateFile(UploadedFile $file, string $directory): string
    {
        $path = Storage::disk($this->privateDiskName())->putFile($directory, $file);

        if (!is_string($path) || $path === '') {
            throw new RuntimeException('Unable to store the uploaded private file.');
        }

        return $path;
    }

    public function publicUrl(string $path): string
    {
        return Storage::disk($this->publicDiskName())->url($path);
    }

    public function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk($this->publicDiskName())->exists($path)) {
            Storage::disk($this->publicDiskName())->delete($path);
        }
    }

    public function deletePrivateFile(?string $path): void
    {
        if ($path && Storage::disk($this->privateDiskName())->exists($path)) {
            Storage::disk($this->privateDiskName())->delete($path);
        }
    }
}
