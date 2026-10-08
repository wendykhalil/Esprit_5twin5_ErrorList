<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'owner')->update(['role' => 'client']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('client')->change();
        });

        $approvedUserIds = DB::table('service_providers')
            ->where('status', 'approved')
            ->pluck('user_id');

        if ($approvedUserIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $approvedUserIds)
                ->where('role', '!=', 'admin')
                ->update(['role' => 'provider']);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'provider')->update(['role' => 'client']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['client', 'owner', 'admin'])
                ->default('client')
                ->change();
        });
    }
};
