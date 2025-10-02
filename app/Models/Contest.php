<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'mode',
        'site_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class);
    }

    public function staffMembers()
    {
        return $this->belongsToMany(User::class, 'contest_user');
    }

    public function registrations()
    {
        return $this->hasMany(ContestRegistration::class);
    }

    public function isActive()
    {
        $now = now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function isPast()
    {
        return $this->end_date < now();
    }

    public function isFuture()
    {
        return $this->start_date > now();
    }
}
