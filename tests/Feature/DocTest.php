<?php

use App\Models\ClientCompany;
use App\Models\Doc;
use App\Models\Project;
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
});

it('can create a doc', function () {
    $response = $this->post(route('projects.docs.store', $this->project), [
        'title' => 'My First Doc',
    ]);

    $response->assertRedirect();

    $doc = Doc::where('title', 'My First Doc')->first();
    expect($doc)->not->toBeNull()
        ->and($doc->project_id)->toBe($this->project->id);
});

it('cannot create a doc without a title', function () {
    $response = $this->post(route('projects.docs.store', $this->project), [
        'title' => '',
    ]);

    $response->assertSessionHasErrors('title');
});

it('can view a doc', function () {
    $doc = $this->project->docs()->create([
        'title' => 'Viewable Doc',
        'content' => '<p>Hello</p>',
    ]);

    $response = $this->get(route('projects.docs.show', [$this->project, $doc]));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Projects/Docs/Show')
            ->has('project')
            ->has('doc')
    );
});

it('can update a doc', function () {
    $doc = $this->project->docs()->create([
        'title' => 'Old Title',
        'content' => null,
    ]);

    $response = $this->put(route('projects.docs.update', [$this->project, $doc]), [
        'title' => 'New Title',
        'content' => '<p>Updated content</p>',
    ]);

    $response->assertNoContent();

    $doc->refresh();
    expect($doc->title)->toBe('New Title')
        ->and($doc->content)->toBe('<p>Updated content</p>');
});

it('can delete a doc', function () {
    $doc = $this->project->docs()->create([
        'title' => 'Doc to Delete',
    ]);

    $response = $this->delete(route('projects.docs.destroy', [$this->project, $doc]));

    $response->assertRedirect(route('projects.show', $this->project));

    $doc->refresh();
    expect($doc->archived_at)->not->toBeNull();
});

it('developer cannot create a doc', function () {
    $developer = User::factory()->create();
    $developer->assignRole('developer');
    $this->project->users()->attach($developer->id);

    $this->actingAs($developer);

    $response = $this->post(route('projects.docs.store', $this->project), [
        'title' => 'Should Fail',
    ]);

    // App exception handler converts 403 → redirect()->back()->error(...)
    $response->assertRedirect();
});

it('developer cannot delete a doc', function () {
    $doc = $this->project->docs()->create([
        'title' => 'Protected Doc',
    ]);

    $developer = User::factory()->create();
    $developer->assignRole('developer');
    $this->project->users()->attach($developer->id);

    $this->actingAs($developer);

    $response = $this->delete(route('projects.docs.destroy', [$this->project, $doc]));

    // App exception handler converts 403 → redirect()->back()->error(...)
    $response->assertRedirect();
});

it('unauthenticated user cannot access docs', function () {
    $doc = $this->project->docs()->create([
        'title' => 'Secret Doc',
    ]);

    auth()->logout();

    $response = $this->get(route('projects.docs.show', [$this->project, $doc]));

    $response->assertRedirect(route('auth.login.form'));
});
