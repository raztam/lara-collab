<?php

namespace App\Actions\Doc;

use App\Events\Doc\DocUpdated;
use App\Models\Doc;

class UpdateDoc
{
    public function update(Doc $doc, array $data): Doc
    {
        $doc->update($data);

        DocUpdated::dispatch($doc);

        return $doc;
    }
}
