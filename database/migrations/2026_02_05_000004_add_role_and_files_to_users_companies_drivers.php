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
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles');
            $table->string('kbis')->nullable()->after('bic_code');
            $table->string('rib')->nullable()->after('kbis');
            $table->string('assurance_rc_pro')->nullable()->after('rib');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles');
            $table->string('driving_license')->nullable()->after('pro_card_expiry_date');
            $table->string('id_card')->nullable()->after('driving_license');
            $table->string('insurance')->nullable()->after('id_card');
            $table->string('vtc_card')->nullable()->after('insurance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['vtc_card', 'insurance', 'id_card', 'driving_license']);
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['assurance_rc_pro', 'rib', 'kbis']);
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
        Schema::dropIfExists('roles');
    }
};
