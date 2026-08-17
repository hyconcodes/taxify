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
        Schema::table('vehicle_owners', function (Blueprint $table) {
            $table->string('state_of_origin')->nullable()->after('address');
            $table->string('driver_license_number')->nullable()->unique()->after('state_of_origin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_owners', function (Blueprint $table) {
            $table->dropColumn(['state_of_origin', 'driver_license_number']);
        });
    }
};
