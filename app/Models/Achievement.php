<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'criteria',
        'contest_id',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    /**
     * Get the contest associated with this achievement (if contest-specific).
     */
    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Get all users who have unlocked this achievement.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}
