<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationCode extends Model
{
    protected $fillable = ['user_id', 'code', 'expires_at', 'attempts'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
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
