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
        'service_type',
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
        'commission_rule_id',
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

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
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

    protected static function booted(): void
    {
        static::saving(function (FormSubmission $submission): void {
            $submission->applyCommissionRule();
        });
    }

    public function applyCommissionRule(): void
    {
        if ($this->activation_status !== 'attivata') {
            return;
        }

        if (in_array($this->commission_status, ['liquidata', 'stornata'], true)) {
            return;
        }

        $rule = CommissionRule::query()
            ->where('service_type', $this->service_type ?: 'sim')
            ->where('trigger_status', 'attivata')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $rule) {
            return;
        }

        $this->commission_rule_id = $rule->id;
        $this->commission_amount = $rule->amount;

        if (! $this->commission_status || $this->commission_status === 'non_maturata') {
            $this->commission_status = 'maturata';
            $this->commission_confirmed_at = $this->commission_confirmed_at ?: now();
        }

        $this->activated_at = $this->activated_at ?: now();
    }
}
