<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedDocument extends Model
{
    public const MODE_DATABASE = 'database';
    public const MODE_LOCAL = 'local';

    protected $fillable = [
        'title',
        'original_name',
        'stored_name',
        'mime_type',
        'extension',
        'size',
        'storage_mode',
        'disk',
        'storage_path',
        'file_data',
        'uploaded_by',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->title ?: $this->original_name;
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max(0, (float) $this->size);

        foreach ($units as $unit) {
            if ($size < 1024 || $unit === 'GB') {
                return number_format($size, $unit === 'B' ? 0 : 2, ',', '.').' '.$unit;
            }

            $size /= 1024;
        }

        return '0 B';
    }

    public function getModeLabelAttribute(): string
    {
        return $this->storage_mode === self::MODE_LOCAL
            ? 'Lokal'
            : 'Database';
    }

    public function isStoredInDatabase(): bool
    {
        return $this->storage_mode === self::MODE_DATABASE;
    }

    public function isStoredLocally(): bool
    {
        return $this->storage_mode === self::MODE_LOCAL;
    }
}
