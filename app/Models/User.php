<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Passport\HasApiTokens;
use App\Notifications\ResetPasswordNotification;




class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public function sendPasswordResetNotification($token): void
    {
        // $url = 'http://127.0.0.1:8000/reset-password/' . $token . '?email=' . $this->email;
        $url = 'http://localhost:5173/add-new-password?token=' . $token . '&email=' . $this->email;
        
        $this->notify(new ResetPasswordNotification($url));
    }

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'id_role',
        'username',
        'password',
        'foto_profile',
        'nama_lengkap',
        'no_telp',
        'email',
        'gender',
        'tanggal_lahir',
        'active',
        'verify_key'
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
}
