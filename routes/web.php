<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharersWizardController;

Route::redirect('/', '/attivazione/dati');

Route::prefix('negozi/{store:slug}/attivazione')->name('stores.wizard.')->group(function () {
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

Route::prefix('attivazione')->name('wizard.')->group(function () {
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
