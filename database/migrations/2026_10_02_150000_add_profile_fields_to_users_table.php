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
            // Contact Information
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            
            // About User
            $table->text('bio')->nullable()->after('country');
            
            // Profile Photo
            $table->string('profile_photo')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'city',
                'country',
                'bio',
                'profile_photo',
            ]);
        });
    }
};
