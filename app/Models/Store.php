<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'contact_name',
        'email',
        'phone',
        'commission_rate',
        'iban',
        'bank_account_holder',
        'is_active',
        // branding
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'font_family',
        'custom_name',
        'custom_domain',
        'custom_texts',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active'       => 'boolean',
        'custom_texts'    => 'array',
    ];

    /* ── Relationships ── */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /* ── Branding helpers ── */

    /** Nome da mostrare nel wizard (custom_name se presente, altrimenti name) */
    public function getDisplayNameAttribute(): string
    {
        return $this->custom_name ?: $this->name;
    }

    /** URL pubblico del logo (null se non caricato) */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    /** URL pubblico del favicon */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon_path ? Storage::url($this->favicon_path) : null;
    }

    /** Il negozio ha branding personalizzato? */
    public function hasBranding(): bool
    {
        return (bool) ($this->logo_path || $this->custom_name || $this->primary_color !== '#dddc00');
    }

    /** Font supportati. 'Arial' è system font (nessun import Google necessario). */
    public static function fontOptions(): array
    {
        return [
            'Arial'      => 'Arial (predefinito)',
            'Inter'      => 'Inter',
            'Roboto'     => 'Roboto',
            'Lato'       => 'Lato',
            'Poppins'    => 'Poppins',
            'Montserrat' => 'Montserrat',
            'Nunito'     => 'Nunito',
        ];
    }

    /** URL Google Fonts da iniettare nel <head> (null se font di sistema) */
    public function getGoogleFontUrlAttribute(): ?string
    {
        if (!$this->font_family || $this->font_family === 'Arial') {
            return null;
        }
        $encoded = urlencode($this->font_family);
        return "https://fonts.googleapis.com/css2?family={$encoded}:wght@400;500;600;700&display=swap";
    }

    /** Testo custom con fallback al default */
    public function customText(string $key, string $default = ''): string
    {
        return (string) data_get($this->custom_texts ?? [], $key, $default);
    }

    /**
     * Blocco <style> con CSS variables di branding da iniettare nel <head>.
     */
    public function getBrandingCssAttribute(): string
    {
        $primary   = e($this->primary_color   ?? '#dddc00');
        $secondary = e($this->secondary_color ?? '#1d1d1b');
        $font      = e($this->font_family      ?? 'Arial');
        $primaryDk = $this->darkenHex($primary, 18);

        return <<<CSS
        <style>
            :root {
                --brand-primary:      {$primary};
                --brand-primary-dark: {$primaryDk};
                --brand-secondary:    {$secondary};
                --brand-font:         '{$font}', Arial, sans-serif;
            }
        </style>
        CSS;
    }

    /** Scurisce un colore HEX di $amount punti per l'hover */
    private function darkenHex(string $hex, int $amount): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = max(0, hexdec(substr($hex, 0, 2)) - $amount);
        $g = max(0, hexdec(substr($hex, 2, 2)) - $amount);
        $b = max(0, hexdec(substr($hex, 4, 2)) - $amount);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
