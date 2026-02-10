<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'company_name',
        'company_address',
        'manager_name',
        'company_zip_code',
        'company_city',
        'company_country',
        'driver_count',
        'company_iban',
        'bic_code',
        'kbis',
        'rib',
        'assurance_rc_pro',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
