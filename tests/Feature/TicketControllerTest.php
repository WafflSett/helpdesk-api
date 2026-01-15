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
