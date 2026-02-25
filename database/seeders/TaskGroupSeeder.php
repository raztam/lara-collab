<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class TaskGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $board = $project->boards()->create([
                'name' => 'Main Board',
                'is_default' => true,
            ]);

            $board->taskGroups()->createMany([
                ['name' => 'Backlog', 'project_id' => $project->id],
                ['name' => 'Todo', 'project_id' => $project->id],
                ['name' => 'In progress', 'project_id' => $project->id],
                ['name' => 'QA', 'project_id' => $project->id],
                ['name' => 'Done', 'project_id' => $project->id],
                ['name' => 'Deployed', 'project_id' => $project->id],
            ]);
        }
    }
}
