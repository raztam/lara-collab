<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_groups', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->after('project_id');
        });

        // Data migration: create a default board for each project and assign task groups
        $projectIds = DB::table('task_groups')->distinct()->pluck('project_id');

        foreach ($projectIds as $projectId) {
            $boardId = DB::table('boards')->insertGetId([
                'project_id' => $projectId,
                'name' => 'Main Board',
                'is_default' => true,
                'order_column' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('task_groups')
                ->where('project_id', $projectId)
                ->update(['board_id' => $boardId]);
        }

        Schema::table('task_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('board_id')->nullable(false)->change();
            $table->foreign('board_id')->references('id')->on('boards')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_groups', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropColumn('board_id');
        });
    }
};
