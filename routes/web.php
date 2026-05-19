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
|--------------------------------------------------------------------------
*/
Route::prefix('negozi/{store:slug}/attivazione')
    ->name('stores.wizard.')
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

/*
|--------------------------------------------------------------------------
| Wizard pubblico (senza store slug — usato anche dai custom domain)
| /attivazione/{step}
|
| Il middleware ResolveCustomDomain, se attivo, inietta il negozio
| nella request in modo trasparente tramite request()->attributes.
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
