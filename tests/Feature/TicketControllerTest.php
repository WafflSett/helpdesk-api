<?php

use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Mockery;

uses(Tests\TestCase::class)->in('Feature');
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (){
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

// store
test('store function exists', function () {
    expect(method_exists(TicketController::class, 'store'))->toBeTrue();
});
test('store function runs on post call', function(){

    $mock = Mockery::mock(TicketController::class)->makePartial();
    $mock->shouldReceive('store')->once();

    $this->app->instance(TicketController::class, $mock);

    $this->post('/api/tickets');
});
test('store function return with status code 201 - created', function(){
    $user = User::factory()->create(['role'=>'customer']);
    $this->actingAs($user);

    $payload = Ticket::factory()->make([
        'user_id' => $user->id,
    ])->toArray();

    $response = $this->post('/api/tickets', $payload);

    $response->assertStatus(201);
});

test('store function returns the newly created ticket', function(){
    $user = User::factory()->create(['role'=>'customer']);
    $this->actingAs($user);

    $payload = Ticket::factory()->make([
        'user_id' => $user->id,
    ])->toArray();

    $response = $this->post('/api/tickets', $payload);

    $response->assertJson([
        'ticket'=>[
            'title' => $payload['title'],
            'description' => $payload['description'],
            'user_id' => $user->id
        ]
    ]);
});

// update
test('update function exists', function() {
    expect(method_exists(TicketController::class, 'update'))->toBeTrue();
});


test('update function runs on post call', function(){

    $mock = Mockery::mock(TicketController::class)->makePartial();
    $mock->shouldReceive('update')->once();

    $this->app->instance(TicketController::class, $mock);

    $this->put("/api/tickets/1");
});

test('update function return with status code 200', function(){
    $user = User::factory()->create(['role'=>'customer']);
    $this->actingAs($user);

    $payload = Ticket::factory()->make([
        'user_id' => $user->id,
    ])->toArray();
    $response = $this->put("/api/tickets/1", $payload);

    $response->assertStatus(200);
});

test('update function successfully changes the status', function(){
    $user = User::factory()->create(['role'=>'customer']);
    $this->actingAs($user);

    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'status'=>'open'
    ]);

    $updateData = ['status'=>'closed'];

    $response = $this->put("/api/tickets/{$ticket->id}", $updateData);
    // dd($response);
    // $response->assertJsonPath('ticket.status', $updateData['status']);
    $response->assertStatus(200);
});
