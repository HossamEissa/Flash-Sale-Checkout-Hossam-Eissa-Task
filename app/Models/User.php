<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }
####################################### Relations ###################################################

####################################### End Relations ###############################################

################################ Accessors and Mutators #############################################

    public function getRoleNameAttribute()
    {
        return $this->roles->first()?->name;
    }

    public function generateOTPCode()
    {
        $this->timestamps = false;
        $this->code = mt_rand(1000, 9999);
        $this->expire_at = now()->addMinutes(5);
        $this->save();
        return $this->code;
    }

    public function resetOTPCode()
    {
        $this->timestamps = false;
        $this->code = null;
        $this->expire_at = null;
        $this->save();
    }


################################ End Accessors and Mutators #########################################
}
