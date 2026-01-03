<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'route_id',
        'user_id',
        'comment',
        'video_url',
        'grade',
        'type',
        'way',
        'verified_by',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope to only include public logs.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to include tentative logs (private).
     */
    public function scopeTentative($query)
    {
        return $query->where('type', 'tentative');
    }
}
