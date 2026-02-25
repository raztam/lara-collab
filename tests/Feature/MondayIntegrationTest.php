<?php

use App\Models\ClientCompany;
use App\Models\MondayIntegration;
use App\Models\Project;
use App\Models\Task;
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

    $clientCompany = ClientCompany::factory()->create();

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

    $this->taskGroup = $this->board->taskGroups()->create([
        'name' => 'Backlog',
        'project_id' => $this->project->id,
    ]);

    $this->integration = MondayIntegration::create([
        'board_id' => $this->board->id,
        'task_group_id' => $this->taskGroup->id,
        'assigned_user_id' => $this->user->id,
        'monday_board_id' => 111111,
        'monday_user_id' => 999,
        'secret' => 'test-secret-token-1234567890123456789',
    ]);
});

// --- Webhook: challenge handshake ---

it('responds to Monday.com challenge verification', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'challenge' => 'abc123xyz',
    ]);

    $response->assertOk()
        ->assertJson(['challenge' => 'abc123xyz']);
});

// --- Webhook: returns 404 for unknown secret ---

it('returns 404 for unknown webhook secret', function () {
    $response = $this->postJson('/api/webhooks/monday/unknown-secret', [
        'event' => ['type' => 'update_column_value'],
    ]);

    $response->assertNotFound();
});

// --- Webhook: ignores non-people-column events ---

it('ignores events that are not update_column_value on multiple-person column', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'event' => [
            'type' => 'create_pulse',
            'pulseName' => 'New task',
        ],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'ignored']);

    expect(Task::count())->toBe(0);
});

it('ignores update_column_value on non-people columns', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'event' => [
            'type' => 'update_column_value',
            'columnType' => 'text',
            'pulseName' => 'Some task',
        ],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'ignored']);

    expect(Task::count())->toBe(0);
});

// --- Webhook: creates task when configured Monday user is assigned ---

it('creates a task when the configured Monday user is assigned', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'event' => [
            'type' => 'update_column_value',
            'columnType' => 'multiple-person',
            'pulseName' => 'Design the homepage',
            'value' => [
                'personsAndTeams' => [
                    ['id' => 999, 'kind' => 'person'],
                ],
            ],
            'previousValue' => [
                'personsAndTeams' => [],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'ok']);

    $task = Task::first();
    expect($task)->not->toBeNull()
        ->and($task->name)->toBe('Design the homepage')
        ->and($task->group_id)->toBe($this->taskGroup->id)
        ->and($task->assigned_to_user_id)->toBe($this->user->id);
});

// --- Webhook: does NOT create task when a different Monday user is assigned ---

it('does not create a task when a different Monday user is assigned', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'event' => [
            'type' => 'update_column_value',
            'columnType' => 'multiple-person',
            'pulseName' => 'Someone else task',
            'value' => [
                'personsAndTeams' => [
                    ['id' => 12345, 'kind' => 'person'],
                ],
            ],
            'previousValue' => [
                'personsAndTeams' => [],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'ignored']);

    expect(Task::count())->toBe(0);
});

// --- Webhook: does NOT create duplicate when user is already in previousValue ---

it('does not create a duplicate task when the Monday user was already assigned', function () {
    $response = $this->postJson('/api/webhooks/monday/test-secret-token-1234567890123456789', [
        'event' => [
            'type' => 'update_column_value',
            'columnType' => 'multiple-person',
            'pulseName' => 'Re-assigned task',
            'value' => [
                'personsAndTeams' => [
                    ['id' => 999, 'kind' => 'person'],
                    ['id' => 888, 'kind' => 'person'],
                ],
            ],
            'previousValue' => [
                'personsAndTeams' => [
                    ['id' => 999, 'kind' => 'person'],
                ],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'ignored']);

    expect(Task::count())->toBe(0);
});

// --- Settings page ---

it('renders the Monday integration settings page', function () {
    $this->actingAs($this->user);

    $response = $this->get(
        route('projects.boards.integrations.monday.show', [$this->project, $this->board])
    );

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Projects/Boards/Integrations/Monday')
            ->has('project')
            ->has('board')
            ->has('integration')
            ->has('webhookUrl')
            ->has('dropdowns.taskGroups')
            ->has('dropdowns.users')
    );
});

it('redirects unauthorized user away from settings page', function () {
    $unauthorized = User::factory()->create();
    $unauthorized->assignRole('client');
    $this->actingAs($unauthorized);

    $response = $this->get(
        route('projects.boards.integrations.monday.show', [$this->project, $this->board])
    );

    // The app's exception handler converts 403 → redirect()->back()->error(...)
    $response->assertRedirect();
});

// --- Save integration ---

it('can save a new Monday integration', function () {
    $this->actingAs($this->user);

    MondayIntegration::where('board_id', $this->board->id)->delete();

    $response = $this->post(
        route('projects.boards.integrations.monday.store', [$this->project, $this->board]),
        [
            'monday_board_id' => 222222,
            'monday_user_id' => 777,
            'task_group_id' => $this->taskGroup->id,
            'assigned_user_id' => $this->user->id,
        ]
    );

    $response->assertRedirect();

    $record = MondayIntegration::where('board_id', $this->board->id)->first();
    expect($record)->not->toBeNull()
        ->and((int) $record->monday_board_id)->toBe(222222)
        ->and((int) $record->monday_user_id)->toBe(777)
        ->and($record->secret)->not->toBeEmpty();
});

it('updating integration preserves the existing secret', function () {
    $this->actingAs($this->user);

    $originalSecret = $this->integration->secret;

    $this->post(
        route('projects.boards.integrations.monday.store', [$this->project, $this->board]),
        [
            'monday_board_id' => 333333,
            'monday_user_id' => 555,
            'task_group_id' => $this->taskGroup->id,
            'assigned_user_id' => $this->user->id,
        ]
    );

    $record = MondayIntegration::where('board_id', $this->board->id)->first();
    expect($record->secret)->toBe($originalSecret)
        ->and((int) $record->monday_board_id)->toBe(333333);
});

// --- Delete integration ---

it('can delete the Monday integration', function () {
    $this->actingAs($this->user);

    $response = $this->delete(
        route('projects.boards.integrations.monday.destroy', [$this->project, $this->board])
    );

    $response->assertRedirect();

    expect(MondayIntegration::where('board_id', $this->board->id)->exists())->toBeFalse();
});
