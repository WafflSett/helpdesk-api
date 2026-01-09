<?php

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

test('show function exists', function () {
    expect(method_exists(TicketController::class, 'show'))->ToBeTrue();
});

test('show method returns the correct status code', function () {
    $user = User::factory()->create(['role' => 'customer']);
    Auth::login($user);

    $ticket = Ticket::create([
        'title' => '$request->title',
        'description' => '$request->description',
        'user_id' => $user->id,
    ]);


    $response = $this->get('/api/tickets/' . $ticket->id);

    $response->assertStatus(200);
});

// test('GET /api/tickets/{ticket} returns the ticket with correct status and JSON', function () {
//     $user = User::factory()->create(['role' => 'customer']);
//     Auth::login($user);

//     $ticket = Ticket::factory()->create([
//         'user_id' => $user->id,
//         'title' => 'My Test Ticket',
//         'description' => 'Ticket description',
//         'status' => 'open',
//     ]);

//     $response = $this->get("/api/tickets/" . $ticket->id);

//     $response->assertJson([
//         'ticket' => [
//             'title' => $ticket['title'],
//             'description' => $ticket['description'],
//             'user_id' => $ticket['user_id'],
//         ]
//     ]);
// });
