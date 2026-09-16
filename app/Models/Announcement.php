<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function isReadBy(int $userId): bool
    {
        return $this->relationLoaded('reads')
            ? $this->reads->contains('user_id', $userId)
            : $this->reads()->where('user_id', $userId)->exists();
    }
}
