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

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Mockery;


uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);


beforeEach(function () {
    $this->withoutMiddleware();
});

// index method tests
test('index function exists', function () {
    expect(method_exists(TicketController::class, 'index'))->ToBeTrue();
});

test('index method is called when hitting /api/tickets', function () {
    $user = User::factory()->create(['role' => 'customer']);
    Auth::login($user);

    $controllerMock = Mockery::mock(TicketController::class)->makePartial();
    $controllerMock->shouldReceive('index')->once();

    $this->app->instance(TicketController::class, $controllerMock);
    $this->get('/api/tickets');
});

test('index method returns the correct status code', function () {
    $user = User::factory()->create(['role' => 'customer']);
    Auth::login($user);

    $response = $this->get('/api/tickets');
    $response->assertStatus(200);
});

test('index method returns the correct JSON response', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    Auth::login($customer);

    $ticket = Ticket::factory()->create(['user_id' => $customer->id]);

    $response = $this->get('/api/tickets');

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $ticket->id]);
});


// show method tests
test('show function exists', function () {
    expect(method_exists(TicketController::class, 'show'))->ToBeTrue();
});

test('show method returns the correct status code 200', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $ticket = Ticket::create([
        'title' => '$request->title',
        'description' => '$request->description',
        'user_id' => $customer->id,
    ]);

    $response = $this->get('/api/tickets/' . $ticket->id);

    $response->assertStatus(200);
});

test('GET /api/tickets/{ticket} returns the ticket with correct status and JSON', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $this->actingAs($user);

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Ticket',
        'description' => 'Ticket description',
        'status' => 'open',
    ]);
    $response = $this->getJson("/api/tickets/{$ticket->id}");

    $response->assertStatus(200);

    $json = $response->json();
    $this->assertArrayHasKey('ticket', $json);

    $fetchedTicket = Ticket::find($ticket->id);
    $this->assertNotNull($fetchedTicket, 'Ticket should exist in database');

    $this->assertEquals('Test Ticket', $fetchedTicket->title);
    $this->assertEquals('Ticket description', $fetchedTicket->description);
    $this->assertEquals($user->id, $fetchedTicket->user_id);
    $this->assertEquals('open', $fetchedTicket->status);
});

test("GET /api/tickets/99999 non-existing ticket", function () {
    $fetchedTicket = Ticket::find(99999);
    $this->assertNull($fetchedTicket, 'Ticket should not exist in database');

});
