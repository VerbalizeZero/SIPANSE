<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\AdminResetPassword;
use App\Notifications\OrtuResetPassword;
use Illuminate\Auth\Notifications\ResetPassword as DefaultResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        // Use custom notification based on role
        if ($this->role === 'orang_tua') {
            $this->notify(new OrtuResetPassword($token));
        } elseif ($this->role === 'tu' || $this->role === 'bendahara') {
            // Use custom notification for TU and Bendahara
            $this->notify(new AdminResetPassword($token));
        } else {
            // Use default Laravel reset password for others
            $this->notify(new DefaultResetPassword($token));
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'nisn',
        'contact',
        'address',
        'is_pic',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'is_pic' => 'boolean',
        ];
    }

    /**
     * Get the siswa associated with the user (for orang_tua role).
     */
    public function siswa()
    {
        return $this->belongsTo(\App\Models\Siswa::class, 'nisn', 'nisn');
    }
}