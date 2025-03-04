<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\HasName;
//use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];
    public function canAccessFilament(): bool
    {
        return str_ends_with($this->email, '@example.com') ;
    }
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'address',
        'password',
        'phone',
        'otp', // تخزين رمز التحقق المؤقت
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    */

    // تعريف العلاقة بين المستخدم والطلبات
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function cart()
{
    return $this->hasMany(Cart::class);
}

}

