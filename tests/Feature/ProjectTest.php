<?php

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {

    $this->auth_user = User::factory()->create();

    $this->assigned_user = User::factory()->create();

    $this->department = Department::factory()->create();

    $this->project = Project::factory()->create();

});

test('aborts invalid requests', function () {

    $this->auth_user->givePermissionTo('create projects');

    $this->actingAs($this->auth_user);

    $this->post('/projects', ['name' => null])->assertInvalid();

});

test('users with the permission can create projects and assign users and departments to it', function () {

    $this->auth_user->givePermissionTo('create projects', 'update users', 'update departments');
    $this->auth_user->assignRole('admin');

    $this->actingAs($this->auth_user);

    $this->post('/projects', [
        'name' => 'TestProject',
        'assigned_user_ids' => [$this->assigned_user->id => ['is_admin' => true]],
        'assigned_departments' => [$this->department]
    ]);

    $this->assertDatabaseHas('projects', [
        'name' => 'TestProject'
    ]);

    $project = Project::where('name', 'TestProject')->first();

    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $this->assigned_user->id
    ]);

    $this->assertDatabaseHas('department_project', [
        'project_id' => $project->id,
        'department_id' => $this->department->id
    ]);
});

test('users without the permission cant create projects', function () {

    $this->actingAs($this->auth_user);

    $this->post('/projects', [
        'name' => 'TestProject',
        'assigned_users' => [$this->assigned_user],
        'assigned_departments' => [$this->department]
    ])->assertStatus(403);
});

test('users can only view projects they are assigned to', function () {

    $this->department->users()->attach($this->auth_user);
    $this->auth_user->departments()->attach($this->department);

    $this->project->departments()->attach($this->department);
    $this->project->users()->attach($this->auth_user);
    $this->department->projects()->attach($this->project);

    $this->auth_user->givePermissionTo('view projects');
    $this->actingAs($this->auth_user);

    $response = $this->get("/projects/{$this->project->id}")
        ->assertInertia(fn(Assert $page) => $page
            ->component('Projects/Show')
            ->has('project', fn(Assert $page) => $page
                ->hasAll(['id', 'name', 'users', 'departments'])
            )
            ->has('project.departments.0', fn(Assert $page) => $page
                ->hasAll('id', 'name', 'users', 'svg_name')
            )
        );

    $response->assertStatus(200);
});

test('users with the permission can update projects', function () {

    $this->auth_user->givePermissionTo('update users', 'update departments');
    $this->actingAs($this->auth_user);

    $this->project->users()->attach($this->auth_user, ['is_admin' => true]);
    $this->auth_user->projects()->attach($this->project, ['is_admin' => true]);

    $this->patch("/projects/{$this->project->id}", [
        'name' => 'TestProject',
        'assigned_user_ids' => [$this->assigned_user->id => ['is_admin' => true]],
        'assigned_departments' => [$this->department]
    ]);

    //dd($res);

    $this->assertDatabaseHas('projects', [
        'name' => 'TestProject'
    ]);

    $project = Project::where('name', 'TestProject')->first();

    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $this->assigned_user->id,
        'is_admin' => 1
    ]);

    $this->assertDatabaseHas('department_project', [
        'project_id' => $project->id,
        'department_id' => $this->department->id
    ]);


});

test('users with the permission can delete projects', function () {

    $this->auth_user->givePermissionTo('delete projects');
    $this->project->users()->attach($this->auth_user, ['is_admin' => true]);
    $this->auth_user->projects()->attach($this->project, ['is_admin' => true]);
    $this->actingAs($this->auth_user);

    $this->delete("/projects/{$this->project->id}");

    $this->assertDatabaseMissing('projects', [
        'id' => $this->project->id
    ]);
});



