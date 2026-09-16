<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Small helper so controllers can fire-and-forget a notification
     * without repeating the create() array shape everywhere.
     */
    public static function send(int $userId, string $type, string $message, ?string $link = null): void
    {
        static::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'link' => $link,
            'created_at' => now(),
        ]);
    }

    /**
     * Alias for send() — some controllers read more naturally calling
     * Notification::notify(...) at the point an event happens.
     */
    public static function notify(int $userId, string $type, string $message, ?string $link = null): void
    {
        static::send($userId, $type, $message, $link);
    }
}
