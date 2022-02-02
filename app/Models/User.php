<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    const INVALIDATE_PASSWORD_RESET_TOKEN = 0;
    const VALIDATE_PASSWORD_RESET_TOKEN = 1;

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_reset_token',
        'password_reset_token_time',
        'password_reset_token_validity',
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
        'password_reset_token_time',
        'password_reset_token_validity',
        'remember_token',
        'deleted_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::retrieved(function($user){
            if($user->isPasswordResetTokenSet()){
                // 15 mins time
                if(time() - strtotime($user->password_reset_token_time) > 900){
                    $user->password_reset_token_validity = User::INVALIDATE_PASSWORD_RESET_TOKEN;
                    $user->save();
                }
            }
        });
    }

    public static function generatePasswordResetToken(){
        return Str::replace('-', '', Str::uuid()).date('His');
    }

    public function isPasswordResetTokenSet(){
        return is_null($this->password_reset_token) ? 0 : 1;
    }

    public function isPasswordResetTokenValid(){
        return $this->password_reset_token_validity == User::VALIDATE_PASSWORD_RESET_TOKEN;
    }

    public function todos(){
        return $this->hasMany(Todo::class, 'user_id', 'id');
    }
}
