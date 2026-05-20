<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionEvent extends Model
{
    protected $fillable = [
        'form_submission_id',
        'performed_by_user_id',
        'event_type',
        'title',
        'description',
        'is_visible_to_customer',
        'occurred_at',
    ];

    protected $casts = [
        'is_visible_to_customer' => 'boolean',
        'occurred_at'            => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    /** Icona Heroicon per il tipo di evento */
    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            'ricevuta'       => 'heroicon-o-inbox',
            'in_lavorazione' => 'heroicon-o-arrow-path',
            'attivata'       => 'heroicon-o-check-circle',
            'respinta'       => 'heroicon-o-x-circle',
            'annullata'      => 'heroicon-o-ban',
            default          => 'heroicon-o-chat-bubble-left-ellipsis',
        };
    }

    /** Colore CSS per la timeline */
    public function getColorAttribute(): string
    {
        return match ($this->event_type) {
            'attivata'            => '#10b981',
            'respinta', 'annullata' => '#ef4444',
            'in_lavorazione'      => '#6366f1',
            'ricevuta'            => '#3b82f6',
            default               => '#9ca3af',
        };
    }
}
