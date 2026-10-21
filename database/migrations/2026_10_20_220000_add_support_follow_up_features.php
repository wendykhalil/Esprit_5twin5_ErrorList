<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->after('closed_at');
            $table->timestamp('first_response_due_at')->nullable()->after('resolved_at');
            $table->timestamp('first_response_at')->nullable()->after('first_response_due_at');
            $table->timestamp('sla_breached_at')->nullable()->after('first_response_at');
            $table->unsignedInteger('reopen_count')->default(0)->after('sla_breached_at');
            $table->timestamp('last_reopened_at')->nullable()->after('reopen_count');
            $table->unsignedTinyInteger('satisfaction_score')->nullable()->after('last_reopened_at');
            $table->text('satisfaction_comment')->nullable()->after('satisfaction_score');
            $table->timestamp('satisfaction_rated_at')->nullable()->after('satisfaction_comment');
            $table->timestamp('last_reminded_at')->nullable()->after('satisfaction_rated_at');
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('is_internal');
        });

        Schema::create('ticket_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')
                ->constrained('support_tickets')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('type', 40);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['support_ticket_id', 'created_at']);
        });

        DB::table('support_tickets')
            ->where('status', 'resolved')
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => DB::raw('COALESCE(closed_at, created_at)'),
            ]);

        DB::table('support_tickets')
            ->where('status', 'resolved')
            ->update(['closed_at' => null]);

        $hours = ['high' => 4, 'normal' => 24, 'low' => 72];

        DB::table('support_tickets')
            ->select('id', 'priority', 'created_at')
            ->orderBy('id')
            ->each(function (object $ticket) use ($hours) {
                DB::table('support_tickets')->where('id', $ticket->id)->update([
                    'first_response_due_at' => Carbon::parse($ticket->created_at)
                        ->addHours($hours[$ticket->priority] ?? 24),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'resolved_at',
                'first_response_due_at',
                'first_response_at',
                'sla_breached_at',
                'reopen_count',
                'last_reopened_at',
                'satisfaction_score',
                'satisfaction_comment',
                'satisfaction_rated_at',
                'last_reminded_at',
            ]);
        });
    }
};
