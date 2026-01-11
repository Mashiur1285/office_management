<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Notepad extends Model
{
    protected $fillable = [
        'user_id',
        'password_hash',
        'content',
        'last_unlocked_at',
    ];

    protected $casts = [
        'last_unlocked_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
        'content', // Hide encrypted content from JSON
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Encrypt content when setting
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = $value ? Crypt::encryptString($value) : null;
    }

    // Decrypt content when getting
    public function getContentAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
