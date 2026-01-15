<?php

use App\Http\Controllers\TicketController;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('delete ticket endpoint exists', function () {
    expect(method_exists(TicketController::class, 'destroy'))->toBeTrue();
});

test('admin can delete a ticket', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $ticket = Ticket::factory()->create([
        'user_id' => $admin->id,
    ]);

    $response = $this
        ->actingAs($admin)
        ->deleteJson("/api/tickets/{$ticket->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('tickets', [
        'id' => $ticket->id,
    ]);
});

test('delete returns 404 if ticket does not exist', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this
        ->actingAs($admin)
        ->deleteJson('/api/tickets/9999');

    $response->assertNotFound();
});

test('customer cannot delete a ticket', function () {
    $owner = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
    ]);

    $customer = User::factory()->create([
        'role' => 'customer',
    ]);

    $response = $this
        ->actingAs($customer)
        ->deleteJson("/api/tickets/{$ticket->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
    ]);
});


test('guest cannot delete a ticket', function () {
    $owner = User::factory()->create();

    $ticket = Ticket::factory()->create([
        'user_id' => $owner->id,
    ]);

    $response = $this->deleteJson("/api/tickets/{$ticket->id}");

    $response->assertUnauthorized();
});
