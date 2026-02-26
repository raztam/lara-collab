<?php

namespace App\Actions\Doc;

use App\Events\Doc\DocCreated;
use App\Models\Doc;
use App\Models\Project;

class CreateDoc
{
    public function create(Project $project, array $data): Doc
    {
        $doc = $project->docs()->create($data);

        DocCreated::dispatch($doc);

        return $doc;
    }
}
