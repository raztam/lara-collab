<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Project;
use App\Models\User;

class BoardPolicy
{
    /**
     * Determine whether the user can view the board.
     */
    public function view(User $user, Board $board, Project $project): bool
    {
        return $user->hasPermissionTo('view projects') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can create boards.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('create task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the board.
     */
    public function update(User $user, Board $board, Project $project): bool
    {
        return $user->hasPermissionTo('edit task group') && $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can delete the board.
     */
    public function delete(User $user, Board $board, Project $project): bool
    {
        return $user->hasPermissionTo('archive task group') && $user->hasProjectAccess($project);
    }
}
