<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Risolve il negozio dal dominio custom della request.
 *
 * Se un negozio ha custom_domain = "attivazioni.mionegozio.it" e il client
 * accede da quel dominio, il negozio viene iniettato negli attributi della
 * request così che il controller possa riconoscerlo anche senza lo slug
 * nell'URL (usando le route /attivazione/{step} già esistenti).
 */
class ResolveCustomDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Salta se siamo sul dominio principale (evita query inutili)
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($host === $appHost || $host === 'localhost') {
            return $next($request);
        }

        $store = Store::query()
            ->where('custom_domain', $host)
            ->where('is_active', true)
            ->first();

        if ($store) {
            $request->attributes->set('resolved_store', $store);
        }

        return $next($request);
    }
}
