<?php

use App\Models\Board;
use App\Models\Project;
use App\Models\TaskGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CountrySeeder::class);
    $this->seed(\Database\Seeders\CurrencySeeder::class);
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->seed(\Database\Seeders\OwnerCompanySeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);

    $clientCompany = \App\Models\ClientCompany::factory()->create();

    $this->project = Project::create([
        'client_company_id' => $clientCompany->id,
        'name' => 'Test Project',
        'default_pricing_type' => 'hourly',
    ]);

    $this->project->users()->attach($this->user->id);

    $this->board = $this->project->boards()->create([
        'name' => 'Main Board',
        'is_default' => true,
    ]);

    $this->board->taskGroups()->createMany([
        ['name' => 'Backlog', 'project_id' => $this->project->id],
        ['name' => 'Todo', 'project_id' => $this->project->id],
    ]);
});

it('creates a default board when creating a project via controller', function () {
    $clientCompany = \App\Models\ClientCompany::factory()->create();

    $response = $this->post(route('projects.store'), [
        'name' => 'New Project',
        'description' => 'Test',
        'client_company_id' => $clientCompany->id,
        'default_pricing_type' => 'hourly',
        'rate' => 50,
        'users' => [$this->user->id],
    ]);

    $response->assertRedirect(route('projects.index'));

    $project = Project::where('name', 'New Project')->first();
    expect($project)->not->toBeNull();

    $defaultBoard = $project->defaultBoard;
    expect($defaultBoard)->not->toBeNull()
        ->and($defaultBoard->name)->toBe('Main Board')
        ->and($defaultBoard->is_default)->toBeTrue();

    $taskGroups = $defaultBoard->taskGroups;
    expect($taskGroups)->toHaveCount(6);
});

it('shows boards index for a project', function () {
    $response = $this->get(route('projects.show', $this->project));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Projects/Boards/Index')
            ->has('project')
            ->has('boards')
    );
});

it('can view a board with task groups and tasks', function () {
    $response = $this->get(route('projects.boards.show', [$this->project, $this->board]));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Projects/Boards/Show')
            ->has('project')
            ->has('board')
            ->has('boards')
            ->has('taskGroups')
            ->has('groupedTasks')
    );
});

it('can create a new board', function () {
    $response = $this->post(route('projects.boards.store', $this->project), [
        'name' => 'Sprint Board',
        'description' => 'For sprint planning',
    ]);

    $response->assertRedirect();

    $board = Board::where('name', 'Sprint Board')->first();
    expect($board)->not->toBeNull()
        ->and($board->project_id)->toBe($this->project->id)
        ->and($board->is_default)->toBeFalse();

    // New board should get default task groups
    expect($board->taskGroups)->toHaveCount(4);
});

it('can update a board', function () {
    $board = $this->project->boards()->create([
        'name' => 'Old Name',
    ]);

    $response = $this->put(route('projects.boards.update', [$this->project, $board]), [
        'name' => 'New Name',
        'description' => 'Updated description',
    ]);

    $response->assertRedirect();

    $board->refresh();
    expect($board->name)->toBe('New Name')
        ->and($board->description)->toBe('Updated description');
});

it('can archive a non-default board', function () {
    $board = $this->project->boards()->create([
        'name' => 'Extra Board',
    ]);

    $response = $this->delete(route('projects.boards.destroy', [$this->project, $board]));

    $response->assertRedirect(route('projects.boards.show', [$this->project, $this->board]));

    $board->refresh();
    expect($board->archived_at)->not->toBeNull();
});

it('cannot archive the default board', function () {
    $response = $this->delete(route('projects.boards.destroy', [$this->project, $this->board]));

    $response->assertRedirect();

    $this->board->refresh();
    expect($this->board->archived_at)->toBeNull();
});

it('scopes task groups to the correct board', function () {
    $board2 = $this->project->boards()->create([
        'name' => 'Board 2',
    ]);

    $board2->taskGroups()->create([
        'name' => 'Board 2 Group',
        'project_id' => $this->project->id,
    ]);

    $response = $this->get(route('projects.boards.show', [$this->project, $this->board]));
    $response->assertSuccessful();

    $taskGroups = $response->original->getData()['page']['props']['taskGroups'];
    $groupNames = collect($taskGroups)->pluck('name')->toArray();

    expect($groupNames)->toContain('Backlog')
        ->toContain('Todo')
        ->not->toContain('Board 2 Group');
});

it('redirects old tasks route to default board', function () {
    $response = $this->get(route('projects.tasks', $this->project));

    $response->assertRedirect(route('projects.boards.show', [$this->project, $this->board]));
});

it('can create a task group under a board', function () {
    $response = $this->post(
        route('projects.boards.task-groups.store', [$this->project, $this->board]),
        ['name' => 'New Group']
    );

    $response->assertRedirect();

    $group = TaskGroup::where('name', 'New Group')->first();
    expect($group)->not->toBeNull()
        ->and($group->board_id)->toBe($this->board->id)
        ->and($group->project_id)->toBe($this->project->id);
});

it('enforces unique board names within a project', function () {
    $response = $this->post(route('projects.boards.store', $this->project), [
        'name' => 'Main Board',
    ]);

    $response->assertSessionHasErrors('name');
});
