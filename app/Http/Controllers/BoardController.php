<?php

namespace App\Http\Controllers;

use App\Http\Requests\Board\StoreBoardRequest;
use App\Http\Requests\Board\UpdateBoardRequest;
use App\Http\Resources\Board\BoardResource;
use App\Http\Resources\TaskPriorityResource;
use App\Models\Board;
use App\Models\Label;
use App\Models\OwnerCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\TaskPriority;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function show(Request $request, Project $project, Board $board, ?Task $task = null): Response
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $groups = $board
            ->taskGroups()
            ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
            ->get();

        $groupedTasks = $board
            ->taskGroups()
            ->with(['project' => fn ($query) => $query->withArchived()])
            ->get()
            ->mapWithKeys(function (TaskGroup $group) use ($request, $project) {
                $prioritySort = data_get($request->input('sort', []), 'priority');

                return [
                    $group->id => Task::where('project_id', $project->id)
                        ->where('group_id', $group->id)
                        ->searchByQueryString()
                        ->filterByQueryString()
                        ->when($request->user()->hasRole('client'), fn ($query) => $query->where('hidden_from_clients', false))
                        ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                        ->when(! $request->has('status'), fn ($query) => $query->whereNull('completed_at'))
                        ->withDefault()
                        ->when($project->isArchived(), fn ($query) => $query->with(['project' => fn ($query) => $query->withArchived()]))
                        ->when($prioritySort, function ($query, $direction) {
                            $direction = $direction === 'asc' ? 'asc' : 'desc';

                            $query
                                ->leftJoin('task_priorities', 'tasks.priority_id', '=', 'task_priorities.id')
                                ->orderByRaw('tasks.priority_id IS NULL')
                                ->orderBy('task_priorities.order', $direction)
                                ->orderByDesc('tasks.created_at')
                                ->select('tasks.*');
                        }, function ($query) {
                            $query->orderByDesc('created_at');
                        })
                        ->get(),
                ];
            });

        return Inertia::render('Projects/Boards/Show', [
            'project' => $project,
            'board' => $board,
            'boards' => BoardResource::collection($project->boards()->ordered()->get()),
            'usersWithAccessToProject' => PermissionService::usersWithAccessToProject($project),
            'labels' => Label::get(['id', 'name', 'color']),
            'priorities' => TaskPriorityResource::collection(TaskPriority::orderBy('order')->get()),
            'taskGroups' => $groups,
            'groupedTasks' => $groupedTasks,
            'openedTask' => $task ? $task->loadDefault() : null,
            'currency' => [
                'symbol' => OwnerCompany::with('currency')->first()?->currency?->symbol,
            ],
        ]);
    }

    public function store(StoreBoardRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [Board::class, $project]);

        $board = $project->boards()->create($request->validated());

        $board->taskGroups()->createMany([
            ['name' => 'Backlog', 'project_id' => $project->id],
            ['name' => 'Todo', 'project_id' => $project->id],
            ['name' => 'In progress', 'project_id' => $project->id],
            ['name' => 'Done', 'project_id' => $project->id],
        ]);

        return redirect()->route('projects.boards.show', [$project, $board])
            ->success('Board created', 'A new board was successfully created.');
    }

    public function update(UpdateBoardRequest $request, Project $project, Board $board): RedirectResponse
    {
        $this->authorize('update', [$board, $project]);

        $board->update($request->validated());

        return redirect()->back()->success('Board updated', 'The board was successfully updated.');
    }

    public function destroy(Project $project, Board $board): RedirectResponse
    {
        $this->authorize('delete', [$board, $project]);

        if ($board->is_default) {
            return redirect()->back()->warning('Action stopped', 'You cannot archive the default board.');
        }

        $board->archive();

        return redirect()->route('projects.boards.show', [$project, $project->defaultBoard])
            ->success('Board archived', 'The board was successfully archived.');
    }
}
