<?php

namespace App\Http\Controllers\Doc;

use App\Actions\Doc\CreateDoc;
use App\Actions\Doc\UpdateDoc;
use App\Events\Doc\DocDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doc\StoreDocRequest;
use App\Http\Requests\Doc\UpdateDocRequest;
use App\Http\Resources\Doc\DocResource;
use App\Models\Doc;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DocController extends Controller
{
    public function show(Project $project, Doc $doc): InertiaResponse
    {
        $this->authorize('view', [$doc, $project]);

        return Inertia::render('Projects/Docs/Show', [
            'project' => $project,
            'doc' => new DocResource($doc),
        ]);
    }

    public function store(StoreDocRequest $request, Project $project, CreateDoc $createDoc): RedirectResponse
    {
        $doc = $createDoc->create($project, $request->validated());

        return redirect()->route('projects.docs.show', [$project, $doc])
            ->success('Doc created', 'A new doc was successfully created.');
    }

    public function update(UpdateDocRequest $request, Project $project, Doc $doc, UpdateDoc $updateDoc): Response
    {
        $updateDoc->update($doc, $request->validated());

        return response()->noContent();
    }

    public function destroy(Project $project, Doc $doc): RedirectResponse
    {
        $this->authorize('delete', [$doc, $project]);

        DocDeleted::dispatch($doc);

        $doc->archive();

        return redirect()->route('projects.show', $project)
            ->success('Doc archived', 'The doc was successfully archived.');
    }
}
