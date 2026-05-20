<?php

use App\Http\Controllers\CustomerTrackingController;
use App\Http\Controllers\SharersWizardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Homepage redirect
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/attivazione/dati');

/*
|--------------------------------------------------------------------------
| Tracking pratica cliente
| Accessibile senza login: /pratica/{token}
|--------------------------------------------------------------------------
*/
Route::get('/pratica/{token}', [CustomerTrackingController::class, 'show'])
    ->name('tracking.show')
    ->where('token', '[a-zA-Z0-9]{48}');

/*
|--------------------------------------------------------------------------
| Wizard con negozio specifico (URL brandizzato per store)
| /negozi/{slug}/attivazione/{step}
|
| NOTA: usare closure invece di [Controller::class, 'method'] perché
| in Laravel 12 il dispatch diretto non lega correttamente {slug}
| quando non è nella firma del metodo controller.
|--------------------------------------------------------------------------
*/
Route::get('negozi/{slug}/attivazione/{step}', fn($slug, $step) =>
    app(SharersWizardController::class)->show($step)
)->name('stores.wizard.show');

Route::post('negozi/{slug}/attivazione/{step}', fn($slug, $step) =>
    app(SharersWizardController::class)->store($step, request())
)->name('stores.wizard.store');

Route::get('negozi/{slug}/attivazione/checkout/concludi-ordine', fn($slug) =>
    app(SharersWizardController::class)->checkout()
)->name('stores.wizard.checkout');

Route::post('negozi/{slug}/attivazione/checkout/paga', fn($slug) =>
    app(SharersWizardController::class)->pay()
)->name('stores.wizard.pay');

Route::post('negozi/{slug}/attivazione/checkout/concludi-offline', fn($slug) =>
    app(SharersWizardController::class)->completeOffline()
)->name('stores.wizard.completeOffline');

Route::get('negozi/{slug}/attivazione/checkout/success', fn($slug) =>
    app(SharersWizardController::class)->success()
)->name('stores.wizard.success');

Route::get('negozi/{slug}/attivazione/checkout/offline-success', fn($slug) =>
    app(SharersWizardController::class)->offlineSuccess()
)->name('stores.wizard.offlineSuccess');

Route::get('negozi/{slug}/attivazione/checkout/cancel', fn($slug) =>
    app(SharersWizardController::class)->cancel()
)->name('stores.wizard.cancel');

Route::post('negozi/{slug}/attivazione/reset', fn($slug) =>
    app(SharersWizardController::class)->reset()
)->name('stores.wizard.reset');

/*
|--------------------------------------------------------------------------
| Wizard pubblico (senza store slug — usato anche dai custom domain)
| /attivazione/{step}
|--------------------------------------------------------------------------
*/
Route::prefix('attivazione')
    ->name('wizard.')
    ->middleware(['resolve.custom.domain'])
    ->group(function () {
        Route::get('{step}', [SharersWizardController::class, 'show'])
            ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
            ->name('show');

        Route::post('{step}', [SharersWizardController::class, 'store'])
            ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
            ->name('store');

        Route::get('checkout/concludi-ordine', [SharersWizardController::class, 'checkout'])->name('checkout');
        Route::post('checkout/paga', [SharersWizardController::class, 'pay'])->name('pay');
        Route::post('checkout/concludi-offline', [SharersWizardController::class, 'completeOffline'])->name('completeOffline');

        Route::get('checkout/success', [SharersWizardController::class, 'success'])->name('success');
        Route::get('checkout/offline-success', [SharersWizardController::class, 'offlineSuccess'])->name('offlineSuccess');
        Route::get('checkout/cancel', [SharersWizardController::class, 'cancel'])->name('cancel');

        Route::post('reset', [SharersWizardController::class, 'reset'])->name('reset');
    });
