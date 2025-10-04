<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestCategory extends Model
{
    protected $fillable = [
        'name',
        'contest_id',
        'type',
        'criteria',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'contest_category_user')->withTimestamps();
    }
}
