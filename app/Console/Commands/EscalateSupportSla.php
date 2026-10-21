<?php

namespace App\Console\Commands;

use App\Models\SupportTicket;
use Illuminate\Console\Command;

class EscalateSupportSla extends Command
{
    protected $signature = 'support:escalate-sla';

    protected $description = 'Monte d\'un niveau la priorité des tickets sans première réponse publique après l\'échéance';

    public function handle(): int
    {
        $escalated = 0;

        SupportTicket::query()
            ->whereNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->where('first_response_due_at', '<', now())
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('id')
            ->each(function (SupportTicket $ticket) use (&$escalated) {
                if ($ticket->priority === 'high' && $ticket->sla_breached_at !== null) {
                    return;
                }

                $from = $ticket->priority;
                $to = match ($from) {
                    'low' => 'normal',
                    'normal' => 'high',
                    default => 'high',
                };

                $ticket->sla_breached_at = $ticket->sla_breached_at ?? now();

                if ($to !== $from) {
                    $ticket->priority = $to;
                    $ticket->first_response_due_at = now()->addHours(SupportTicket::SLA_HOURS[$to]);
                }

                $ticket->save();
                $ticket->recordEvent('sla_escalated', null, [
                    'from' => $from,
                    'to' => $to,
                ]);
                $escalated++;
            });

        $this->info($escalated.' ticket(s) traité(s).');

        return self::SUCCESS;
    }
};
