<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MondayIntegration extends Model
{
    protected $fillable = [
        'board_id',
        'task_group_id',
        'assigned_user_id',
        'monday_board_id',
        'monday_user_id',
        'secret',
    ];

    protected static function booted(): void
    {
        static::creating(function (MondayIntegration $integration): void {
            if (empty($integration->secret)) {
                $integration->secret = Str::random(40);
            }
        });
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function taskGroup(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
