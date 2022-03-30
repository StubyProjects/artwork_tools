<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('users can view users if they have the right to', function () {

    $user = User::factory()->create();

    $user->assignRole('admin');

    $this->actingAs($user);

    $response = $this->get('/users')
        ->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Users/Index')
            ->has('users.data', 1)
            ->has('users.data.0', fn(AssertableInertia $page) => $page
                ->hasAll([
                        'name',
                        'email',
                        'phone_number',
                        'position',
                        'business',
                    ])->etc()
            )
            ->where('users.per_page', 15)
        );

    $response->assertStatus(200);

    $user->removeRole('admin');
    $user->givePermissionTo('view users');

    $response = $this->get('/users')
        ->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Users/Index')
            ->has('users.data', 1)
            ->has('users.data.0', fn(AssertableInertia $page) => $page
                ->hasAll([
                    'name',
                    'email',
                    'phone_number',
                    'position',
                    'business',
                ])->etc()
            )
            ->where('users.per_page', 15)
        );

    $response->assertStatus(200);
});

test('users cannot view all users without permission', function () {

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/users');

    $response->assertStatus(403);
});

test('users can update update other users', function () {

    $user = User::factory()->create();

    $user_to_edit = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->patch("/users/{$user_to_edit->id}", [
        "name" => "Benjamin",
        "position" => "CEO",
        "business" => "DTH",
        "phone_number" => "1337",
        "description" => "Description was changed"
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('users', [
        "id" => $user_to_edit->id,
        "name" => "Benjamin",
        "position" => "CEO",
        "business" => "DTH",
        "phone_number" => "1337",
        "description" => "Description was changed"
    ]);

    $user->removeRole('admin');
    $user->givePermissionTo('update users');

    $response = $this->patch("/users/{$user_to_edit->id}", [
        "name" => "Benjamin",
        "position" => "CEO",
        "business" => "DTH",
        "phone_number" => "1337",
        "description" => "Description was changed"
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('users', [
        "id" => $user_to_edit->id,
        "name" => "Benjamin",
        "position" => "CEO",
        "business" => "DTH",
        "phone_number" => "1337",
        "description" => "Description was changed"
    ]);

});

test('users cannot update users without permission', function () {

    $user = User::factory()->create();

    $user_to_edit = User::factory()->create();

    $this->actingAs($user);

    $this->patch("/users/{$user_to_edit->id}", [
        "name" => "Benjamin",
        "position" => "CEO",
        "business" => "DTH",
        "phone_number" => "1337",
        "description" => "Description was changed"
    ])->assertForbidden();

});

test('users can delete other users', function () {

    $user = User::factory()->create();

    $user_to_edit = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->delete("/users/{$user_to_edit->id}");

    $response->assertStatus(302);

    $this->assertDatabaseMissing('users', [
        "id" => $user_to_edit->id,
    ]);

    $user_to_edit = User::factory()->create();
    $user->removeRole('admin');
    $user->givePermissionTo('delete users');

    $response = $this->delete("/users/{$user_to_edit->id}");

    $response->assertStatus(302);

    $this->assertDatabaseMissing('users', [
        "id" => $user_to_edit->id,
    ]);

});

test('consultants cannot delete any client', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    $user_to_edit = User::factory()->create();

    $this->delete("/users/{$user_to_edit->id}")->assertStatus(403);
});