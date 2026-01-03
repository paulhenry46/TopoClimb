<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasProfilePhoto;

    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'birth_date',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class);
    }

    public function hr()
    {
        $roles_name = $this->getRoleNames()->toArray();
        if (in_array('super-admin', $roles_name)) {
            return 0;
        } elseif (preg_grep('/^owner/', $roles_name)) {
            return 1;
        } elseif (preg_grep('/^admin/', $roles_name)) {
            return 2;
        } elseif (preg_grep('/^opener/', $roles_name)) {
            return 3;
        } else {
            return 4;
        }
    }

    public function favoriteSites()
    {

        return $this->belongsToMany(Site::class);

    }

    public function registeredRoutes()
    {
        return $this->belongsToMany(Route::class, 'registered_routes_users');
    }

    public function verifiedLogs()
    {
        return $this->hasMany(Log::class, 'verified_by');
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function contestCategories()
    {
        return $this->belongsToMany(ContestCategory::class, 'contest_category_user')->withTimestamps();
    }

    public function getAge()
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
            ->withTimestamps();
    }

    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get all achievements unlocked by this user.
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    /**
     * Check if the user has unlocked a specific achievement.
     */
    public function hasAchievement($achievementKey)
    {
        return $this->achievements()->where('key', $achievementKey)->exists();
    }

    /**
     * Get the user's statistics.
     */
    public function stats()
    {
        return $this->hasOne(UserStats::class);
    }
}
