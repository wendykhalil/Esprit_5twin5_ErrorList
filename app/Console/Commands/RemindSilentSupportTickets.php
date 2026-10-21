<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Console\Command;

class RemindSilentSupportTickets extends Command
{
    protected $signature = 'support:remind-silent';

    protected $description = 'Ajoute une note interne sur les tickets sans réponse publique depuis 48 heures';

    public function handle(): int
    {
        $admin = User::query()->where('role', UserRole::Admin->value)->orderBy('id')->first();

        if ($admin === null) {
            $this->error('Aucun administrateur disponible pour signer la relance.');

            return self::FAILURE;
        }

        $limit = now()->subHours(48);
        $reminded = 0;

        SupportTicket::query()
            ->whereIn('status', ['open', 'in_progress'])
            ->where('created_at', '<=', $limit)
            ->where(function ($query) use ($limit) {
                $query->whereNull('last_reminded_at')
                    ->orWhere('last_reminded_at', '<=', $limit);
            })
            ->whereDoesntHave('publicReplies', function ($query) use ($limit) {
                $query->where('created_at', '>', $limit);
            })
            ->orderBy('id')
            ->each(function (SupportTicket $ticket) use ($admin, &$reminded) {
                $ticket->replies()->create([
                    'user_id' => $admin->id,
                    'body' => 'Relance automatique : aucune réponse publique depuis 48 heures.',
                    'is_internal' => true,
                ]);

                $ticket->last_reminded_at = now();
                $ticket->save();
                $ticket->recordEvent('reminded', $admin->id);
                $reminded++;
            });

        $this->info($reminded.' relance(s) ajoutée(s).');

        return self::SUCCESS;
    }
};
