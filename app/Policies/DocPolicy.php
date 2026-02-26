<?php

namespace App\Policies;

use App\Models\Doc;
use App\Models\Project;
use App\Models\User;

class DocPolicy
{
    /**
     * Determine whether the user can view the doc.
     */
    public function view(User $user, Doc $doc, Project $project): bool
    {
        return $user->hasPermissionTo('view projects') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can create docs.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('create task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the doc.
     */
    public function update(User $user, Doc $doc, Project $project): bool
    {
        return $user->hasPermissionTo('edit task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the doc.
     */
    public function delete(User $user, Doc $doc, Project $project): bool
    {
        return $user->hasPermissionTo('archive task group') && $user->hasProjectAccess($project);
    }
}
