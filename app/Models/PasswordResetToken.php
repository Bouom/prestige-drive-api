<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email'; // Tell Laravel about your primary key
    public $incrementing = false; // Since it's not auto-incrementing
    protected $keyType = 'string'; 

    protected $fillable = ['email', 'token', 'verification_token', 'expires_at', 'attempts', 'ip_address'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    public function isValid()
    {
        return $this->expires_at->isFuture() && $this->attempts < 5;
    }
    
    public function incrementAttempts()
    {
        $this->attempts++;
        return $this->save();
    }
}
