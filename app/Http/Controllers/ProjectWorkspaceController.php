<?php

namespace App\Http\Controllers;

use App\Http\Resources\Board\BoardResource;
use App\Http\Resources\Doc\DocResource;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectWorkspaceController extends Controller
{
    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $boards = $project->boards()
            ->withCount(['taskGroups', 'tasks' => fn ($q) => $q->whereNull('completed_at')])
            ->ordered()
            ->get();

        $docs = $project->docs()->ordered()->get(['id', 'title']);

        return Inertia::render('Projects/Boards/Index', [
            'project' => $project,
            'boards' => BoardResource::collection($boards),
            'docs' => DocResource::collection($docs),
        ]);
    }
}
