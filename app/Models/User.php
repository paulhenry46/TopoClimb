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
    use HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
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
        'google_id'
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
        ];
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class);
    }

    public function hr(){
        $roles_name = $this->getRoleNames()->toArray();
            if(in_array('super-admin', $roles_name)){
                return 0;
            }elseif(preg_grep("/^owner/", $roles_name)){
                return  1;
            }elseif(preg_grep("/^admin/", $roles_name)){
                return  2;
            }elseif(preg_grep("/^opener/", $roles_name)){
                return 3;
            }else{
                return 4;
            }
    }

    public function favoriteSites(){

        return $this->belongsToMany(Site::class);

    }

    public function registeredRoutes(){
        return $this->belongsToMany(Route::class, 'registered_routes_users');
   }

}
