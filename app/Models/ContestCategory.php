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
        'auto_assign',
        'min_age',
        'max_age',
        'gender',
    ];

    protected $casts = [
        'auto_assign' => 'boolean',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'contest_category_user')->withTimestamps();
    }

    public function userMatches(User $user)
    {
        if (!$this->auto_assign) {
            return false;
        }

        // Check gender field (new approach - takes precedence over type/criteria)
        if ($this->gender && $this->gender !== 'all') {
            if (!$user->gender || strtolower($user->gender) !== strtolower($this->gender)) {
                return false;
            }
        }

        // Backward compatibility: Check gender if type is gender (old approach)
        if (!$this->gender && $this->type === 'gender' && $this->criteria) {
            if (!$user->gender || strtolower($user->gender) !== strtolower($this->criteria)) {
                return false;
            }
        }

        // Check age if age range is defined
        if ($this->min_age !== null || $this->max_age !== null) {
            $age = $user->getAge();
            if ($age === null) {
                return false;
            }

            if ($this->min_age !== null && $age < $this->min_age) {
                return false;
            }

            if ($this->max_age !== null && $age > $this->max_age) {
                return false;
            }
        }

        return true;
    }
}
