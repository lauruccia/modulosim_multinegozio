<?php

namespace App\Providers;

use App\Models\FormSubmission;
use App\Observers\SubmissionObserver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registra l'observer che crea eventi automatici al cambio stato pratica
        FormSubmission::observe(SubmissionObserver::class);
    }
}
