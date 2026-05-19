<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    protected $fillable = [
        'store_id',
        'submitted_by_user_id',
        'source',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'activation_status',
        'activated_at',
        'payment_method',
        'payment_status',
        'total_amount',
        'commission_amount',
        'commission_status',
        'commission_confirmed_at',
        'commission_paid_at',
        'admin_notes',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'total_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'activated_at' => 'datetime',
        'commission_confirmed_at' => 'datetime',
        'commission_paid_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->store_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('store_id', $user->store_id);
    }
}
