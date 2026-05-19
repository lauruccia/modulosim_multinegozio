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

// DIAGNOSTICA TEMPORANEA — rimuovere dopo il test
Route::get('/debug-routes', function () {
    $all = collect(app('router')->getRoutes())->map(fn($r) => $r->uri());
    return response()->json([
        'negozi_routes' => $all->filter(fn($u) => str_contains($u, 'negozi'))->values(),
        'store_4tacche' => \App\Models\Store::where('slug', '4tacche')->first()?->only(['id','slug','is_active']),
    ]);
});

// Testa il model binding con :slug
Route::get('/debug-bind/{store:slug}', function (\App\Models\Store $store) {
    return response()->json($store->only(['id','slug','is_active','name']));
});

// Testa il rendering della view wizard
Route::get('/debug-view', function () {
    $store = \App\Models\Store::where('slug', '4tacche')->first();
    $steps = ['dati','documento','contatti','indirizzi','numero','servizi','pagamento'];
    try {
        $html = view('wizard.dati', ['step'=>'dati','steps'=>$steps,'data'=>[],'store'=>$store])->render();
        return response('VIEW OK — ' . strlen($html) . ' bytes');
    } catch (\Throwable $e) {
        return response()->json(['error'=>$e->getMessage(),'file'=>basename($e->getFile()),'line'=>$e->getLine()], 500);
    }
});

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
