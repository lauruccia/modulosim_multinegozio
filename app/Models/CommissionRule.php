<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type',
        'trigger_status',
        'amount',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function serviceOptions(): array
    {
        return [
            'sim' => 'Attivazione SIM',
            'luce' => 'Attivazione luce',
            'gas' => 'Attivazione gas',
            'luce_gas' => 'Attivazione luce + gas',
            'altro' => 'Altro servizio',
        ];
    }

    public static function triggerOptions(): array
    {
        return [
            'attivata' => 'Attivazione conclusa',
            'completata' => 'Pratica completata',
        ];
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
