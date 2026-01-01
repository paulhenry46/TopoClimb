<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    protected $fillable = [
        'user_id',
        'achievement_id',
        'unlocked_at',
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    /**
     * Get the user who unlocked this achievement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the achievement that was unlocked.
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}
