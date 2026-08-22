<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'code',
    'category_id',
    'module_id',
    'user_id',
    'document_type',
    'document_number',
    'name',
    'is_priority',
    'status',
    'call_count',
    'called_at',
    'started_at',
    'ended_at',
    'cancelled_at',
    'idempotency_key',
])]
class Ticket extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => TicketStatus::class,
            'is_priority' => 'boolean',
            'call_count' => 'integer',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}