<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelArchivable\Archivable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Doc extends Model implements AuditableContract, Sortable
{
    use Archivable, Auditable, HasFactory, SortableTrait;

    protected $fillable = [
        'project_id',
        'title',
        'content',
        'order_column',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
