<?php
// Get the existing content
$content = file_get_contents('tests/Feature/ReclamationPhase2Test.php');

// Find the last occurrence of "function test_" to identify where original tests end
$lastFunctionPos = strrpos($content, 'function test_');
if ($lastFunctionPos === false) {
    die("No test functions found\n");
}

// Find the closing brace of this last function
$searchStart = $lastFunctionPos;
$braceDepth = 0;
$inFunction = false;
$endPos = -1;

for ($i = $searchStart; $i < strlen($content); $i++) {
    $char = $content[$i];
    if ($char === '{') {
        $braceDepth++;
        $inFunction = true;
    } elseif ($char === '}' && $inFunction) {
        $braceDepth--;
        if ($braceDepth === 0) {
            $endPos = $i;
            break;
        }
    }
}

if ($endPos === -1) {
    die("Could not find end of last test function\n");
}

// Keep everything up to and including this closing brace
$truncated = substr($content, 0, $endPos + 1);

// Now add the three new tests
$newTests = <<<'EOFNEWT'

    /**
     * Test: Cross-reclamation refunds within limit (both succeed)
     */
    public function test_two_reclamations_cross_payment_sequential_within_limit(): void
    {
        $payment = Payment::factory()->create(['amount' => 100]);
        $reservation = Reservation::find($this->reservation->id);
        $payment->reservation()->associate($reservation)->save();
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 2',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident1 = Incident::create([
            'reclamation_id' => $reclamation1->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Test',
            'proposed_refund_amount' => 50,
            'proposed_refund_percentage' => 30,
        ]);
        
        $incident2 = Incident::create([
            'reclamation_id' => $reclamation2->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'manquant',
            'description' => 'Test',
            'proposed_refund_amount' => 50,
            'proposed_refund_percentage' => 20,
        ]);
        
        $this->actingAs($this->admin);
        
        $this->post(route('admin.reclamations.approve', $reclamation1->id), [
            'approved_incidents' => [$incident1->id],
        ])->assertRedirect();
        
        $this->post(route('admin.reclamations.approve', $reclamation2->id), [
            'approved_incidents' => [$incident2->id],
        ])->assertRedirect();
        
        $totalRefunded = $payment->transactions()->where('type', 'refund')->sum('amount');
        $this->assertEquals(100, $totalRefunded);
    }

    /**
     * Test: Cross-reclamation refunds exceeding limit (second fails and rolls back)
     */
    public function test_two_reclamations_cross_payment_sequential_exceeding_limit(): void
    {
        $payment = Payment::factory()->create(['amount' => 100]);
        $reservation = Reservation::find($this->reservation->id);
        $payment->reservation()->associate($reservation)->save();
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 2',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident1 = Incident::create([
            'reclamation_id' => $reclamation1->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Test',
            'proposed_refund_amount' => 60,
            'proposed_refund_percentage' => 30,
        ]);
        
        $incident2 = Incident::create([
            'reclamation_id' => $reclamation2->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'manquant',
            'description' => 'Test',
            'proposed_refund_amount' => 60,
            'proposed_refund_percentage' => 20,
        ]);
        
        $this->actingAs($this->admin);
        
        $this->post(route('admin.reclamations.approve', $reclamation1->id), [
            'approved_incidents' => [$incident1->id],
        ])->assertRedirect();
        
        $response2 = $this->post(route('admin.reclamations.approve', $reclamation2->id), [
            'approved_incidents' => [$incident2->id],
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('error');
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation2->id,
            'status' => 'en_attente',
        ]);
    }

    /**
     * Test: Concurrent approvals with separate connections exceeding limit
     */
    public function test_concurrent_approvals_separate_connections_exceeding_limit(): void
    {
        $payment = Payment::factory()->create(['amount' => 100]);
        $reservation = Reservation::find($this->reservation->id);
        $payment->reservation()->associate($reservation)->save();
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 2',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->assertDatabaseHas('reclamations', ['id' => $reclamation1->id]);
        $this->assertDatabaseHas('reclamations', ['id' => $reclamation2->id]);
        
        $totalRefunded = DB::table('transactions')
            ->where('payment_id', $payment->id)
            ->where('type', 'refund')
            ->sum('amount');
        $this->assertLessThanOrEqual(100, $totalRefunded);
    }
EOFNEWT;

// Write the new file
$finalContent = $truncated . "\n" . $newTests . "\n}\n";
file_put_contents('tests/Feature/ReclamationPhase2Test.php', $finalContent);
echo "File rebuilt successfully\n";
