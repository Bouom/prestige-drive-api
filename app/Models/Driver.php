<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'is_available',
        'license_type',
        'experience',
        'insurance_issue_date',
        'insurance_expiry_date',
        'id_issue_date',
        'id_expiry_date',
        'license_issue_date',
        'license_expiry_date',
        'pro_card_issue_date',
        'pro_card_expiry_date',
        'driving_license',
        'id_card',
        'insurance',
        'vtc_card',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'insurance_issue_date' => 'date',
        'insurance_expiry_date' => 'date',
        'id_issue_date' => 'date',
        'id_expiry_date' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'pro_card_issue_date' => 'date',
        'pro_card_expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
