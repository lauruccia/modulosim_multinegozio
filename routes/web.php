<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OptimaWizardController;

Route::redirect('/', '/attivazione/dati');

Route::prefix('negozi/{store:slug}/attivazione')->name('stores.wizard.')->group(function () {
    Route::get('{step}', [OptimaWizardController::class, 'show'])
        ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
        ->name('show');

    Route::post('{step}', [OptimaWizardController::class, 'store'])
        ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
        ->name('store');

    Route::get('checkout/concludi-ordine', [OptimaWizardController::class, 'checkout'])->name('checkout');
    Route::post('checkout/paga', [OptimaWizardController::class, 'pay'])->name('pay');
    Route::post('checkout/concludi-offline', [OptimaWizardController::class, 'completeOffline'])->name('completeOffline');

    Route::get('checkout/success', [OptimaWizardController::class, 'success'])->name('success');
    Route::get('checkout/offline-success', [OptimaWizardController::class, 'offlineSuccess'])->name('offlineSuccess');
    Route::get('checkout/cancel', [OptimaWizardController::class, 'cancel'])->name('cancel');

    Route::post('reset', [OptimaWizardController::class, 'reset'])->name('reset');
});

Route::prefix('attivazione')->name('wizard.')->group(function () {
    Route::get('{step}', [OptimaWizardController::class, 'show'])
        ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
        ->name('show');

    Route::post('{step}', [OptimaWizardController::class, 'store'])
        ->whereIn('step', ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'])
        ->name('store');

    Route::get('checkout/concludi-ordine', [OptimaWizardController::class, 'checkout'])->name('checkout');
    Route::post('checkout/paga', [OptimaWizardController::class, 'pay'])->name('pay');
    Route::post('checkout/concludi-offline', [OptimaWizardController::class, 'completeOffline'])->name('completeOffline');

    Route::get('checkout/success', [OptimaWizardController::class, 'success'])->name('success');
    Route::get('checkout/offline-success', [OptimaWizardController::class, 'offlineSuccess'])->name('offlineSuccess');
    Route::get('checkout/cancel', [OptimaWizardController::class, 'cancel'])->name('cancel');

    Route::post('reset', [OptimaWizardController::class, 'reset'])->name('reset');
});
