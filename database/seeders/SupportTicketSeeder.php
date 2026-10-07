<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            $admin = User::factory()->create([
                'name' => 'Administrateur SolarShare',
                'email' => 'admin@solarshare.tn',
                'role' => 'admin',
            ]);
        }

        $clients = User::query()
            ->where('role', '!=', 'admin')
            ->take(5)
            ->get();

        if ($clients->isEmpty()) {
            $clients = User::factory(3)->create(['role' => 'client']);
        }

        SupportTicket::factory(10)
            ->state(fn () => ['user_id' => $clients->random()->id])
            ->create()
            ->each(function (SupportTicket $ticket) use ($admin, $clients) {
                $replyCount = random_int(0, 5);

                for ($i = 0; $i < $replyCount; $i++) {
                    $isInternal = fake()->boolean(20);

                    TicketReply::factory()->create([
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $isInternal ? $admin->id : fake()->randomElement([$ticket->user_id, $admin->id]),
                        'is_internal' => $isInternal,
                    ]);
                }
            });
    }
}
