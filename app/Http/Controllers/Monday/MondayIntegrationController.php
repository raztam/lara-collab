<?php

namespace App\Http\Controllers\Monday;

use App\Http\Controllers\Controller;
use App\Http\Requests\Monday\StoreMondayIntegrationRequest;
use App\Models\Board;
use App\Models\MondayIntegration;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MondayIntegrationController extends Controller
{
    public function show(Project $project, Board $board): Response
    {
        $this->authorize('update', [$board, $project]);

        $integration = MondayIntegration::where('board_id', $board->id)->first();

        return Inertia::render('Projects/Boards/Integrations/Monday', [
            'project' => $project,
            'board' => $board,
            'integration' => $integration,
            'webhookUrl' => $integration ? rtrim(config('app.url'), '/')."/api/webhooks/monday/{$integration->secret}" : null,
            'dropdowns' => [
                'taskGroups' => $board->taskGroups()
                    ->get(['id', 'name'])
                    ->map(fn ($g) => ['value' => (string) $g->id, 'label' => $g->name])
                    ->toArray(),
                'users' => User::userDropdownValues(),
            ],
        ]);
    }

    public function store(StoreMondayIntegrationRequest $request, Project $project, Board $board): RedirectResponse
    {
        $this->authorize('update', [$board, $project]);

        $validated = $request->validated();

        MondayIntegration::updateOrCreate(
            ['board_id' => $board->id],
            [
                'task_group_id' => $validated['task_group_id'],
                'assigned_user_id' => $validated['assigned_user_id'],
                'monday_board_id' => $validated['monday_board_id'],
                'monday_user_id' => $validated['monday_user_id'],
            ]
        );

        return redirect()->back()->success('Integration saved', 'Monday.com integration has been configured.');
    }

    public function destroy(Project $project, Board $board): RedirectResponse
    {
        $this->authorize('update', [$board, $project]);

        MondayIntegration::where('board_id', $board->id)->delete();

        return redirect()->back()->success('Integration removed', 'Monday.com integration has been removed.');
    }
}
