<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('accepted_reply_id')
                ->nullable()
                ->unique()
                ->after('closed_at')
                ->constrained('ticket_replies')
                ->nullOnDelete();

            $table->string('ai_category', 32)->nullable()->after('accepted_reply_id');
            $table->enum('ai_suggested_priority', ['low', 'normal', 'high'])->nullable()->after('ai_category');
            $table->text('ai_summary')->nullable()->after('ai_suggested_priority');
            $table->text('ai_suggested_reply')->nullable()->after('ai_summary');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_suggested_reply');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accepted_reply_id');
            $table->dropColumn([
                'ai_category',
                'ai_suggested_priority',
                'ai_summary',
                'ai_suggested_reply',
                'ai_analyzed_at',
            ]);
        });
    }
};
