<?php

namespace App\Http\Controllers\Board;

use App\Actions\Task\CreateTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project, Board $board): RedirectResponse
    {
        $this->authorize('create', [Task::class, $project]);

        (new CreateTask)->create($project, $request->validated());

        return redirect()->route('projects.boards.show', [$project, $board])
            ->success('Task added', 'A new task was successfully added.');
    }
}
