<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Infos Société
        $table->string('company_name')->nullable();
        $table->string('company_address')->nullable();
        $table->string('manager_name')->nullable();
        $table->string('company_zip_code')->nullable();
        $table->string('company_city')->nullable();
        $table->string('company_country')->nullable();
        $table->integer('driver_count')->default(1);
        $table->string('company_iban')->nullable();
        $table->string('bic_code')->nullable();
        $table->date('insurance_issue_date')->nullable();
        $table->date('insurance_expiry_date')->nullable();

        // Infos Chauffeur
        $table->boolean('is_available')->default(true);
        $table->string('license_type')->nullable();
        $table->string('experience')->nullable();
        $table->date('id_issue_date')->nullable();
        $table->date('id_expiry_date')->nullable();
        $table->date('license_issue_date')->nullable();
        $table->date('license_expiry_date')->nullable();
        $table->date('pro_card_issue_date')->nullable();
        $table->date('pro_card_expiry_date')->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'company_name', 'company_address', 'manager_name', 'company_zip_code',
            'company_city', 'company_country', 'driver_count', 'company_iban', 'bic_code',
            'insurance_issue_date', 'insurance_expiry_date', 'is_available', 'license_type',
            'experience', 'id_issue_date', 'id_expiry_date', 'license_issue_date',
            'license_expiry_date', 'pro_card_issue_date', 'pro_card_expiry_date'
        ]);
    });
}
};
