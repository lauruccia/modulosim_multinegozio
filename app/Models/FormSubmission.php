<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormSubmission extends Model
{
    protected $fillable = [
        'tracking_token',
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
        'tracking_notified_at',
        'admin_notes',
        'payload',
    ];

    protected $casts = [
        'payload'                 => 'array',
        'total_amount'            => 'decimal:2',
        'commission_amount'       => 'decimal:2',
        'activated_at'            => 'datetime',
        'commission_confirmed_at' => 'datetime',
        'commission_paid_at'      => 'datetime',
        'tracking_notified_at'    => 'datetime',
    ];

    /* ── Relationships ── */

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

    public function events(): HasMany
    {
        return $this->hasMany(SubmissionEvent::class)->orderBy('occurred_at');
    }

    /* ── Accessors ── */

    /** URL pubblico di tracking per il cliente */
    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->tracking_token) {
            return null;
        }
        return route('tracking.show', $this->tracking_token);
    }

    /** Etichetta leggibile dello stato attivazione */
    public function getActivationStatusLabelAttribute(): string
    {
        return match ($this->activation_status) {
            'richiesta'     => 'Richiesta ricevuta',
            'in_lavorazione' => 'In lavorazione',
            'attivata'      => 'Attivata',
            'respinta'      => 'Respinta',
            'annullata'     => 'Annullata',
            default         => ucfirst($this->activation_status ?? ''),
        };
    }

    /* ── Scopes ── */

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

    /* ── Boot ── */

    protected static function booted(): void
    {
        // Genera il tracking token alla creazione
        static::creating(function (FormSubmission $submission): void {
            if (empty($submission->tracking_token)) {
                $submission->tracking_token = Str::random(48);
            }
        });

        static::saving(function (FormSubmission $submission): void {
            $submission->applyCommissionRule();
        });
    }

    /* ── Business logic ── */

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
        $this->commission_amount  = $rule->amount;

        if (! $this->commission_status || $this->commission_status === 'non_maturata') {
            $this->commission_status        = 'maturata';
            $this->commission_confirmed_at  = $this->commission_confirmed_at ?: now();
        }

        $this->activated_at = $this->activated_at ?: now();
    }

    /**
     * Crea un evento di sistema sulla pratica.
     * Usato dall'Observer e dalle azioni Filament.
     */
    public function addEvent(
        string $eventType,
        string $title,
        ?string $description = null,
        bool $visibleToCustomer = true
    ): SubmissionEvent {
        return $this->events()->create([
            'event_type'             => $eventType,
            'title'                  => $title,
            'description'            => $description,
            'is_visible_to_customer' => $visibleToCustomer,
            'occurred_at'            => now(),
        ]);
    }
}
