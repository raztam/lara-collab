<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monday_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('task_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('monday_board_id');
            $table->unsignedBigInteger('monday_user_id');
            $table->string('secret', 64)->unique();
            $table->timestamps();

            $table->unique('monday_board_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monday_integrations');
    }
};
