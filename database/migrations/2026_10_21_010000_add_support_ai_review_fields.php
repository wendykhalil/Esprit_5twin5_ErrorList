<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->text('ai_priority_reason')->nullable()->after('ai_suggested_reply');
            $table->json('ai_missing_information')->nullable()->after('ai_priority_reason');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('ai_missing_information');
            $table->string('ai_context_hash', 64)->nullable()->after('ai_confidence');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'ai_priority_reason',
                'ai_missing_information',
                'ai_confidence',
                'ai_context_hash',
            ]);
        });
    }
};
