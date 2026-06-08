<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\Store;
use App\Mail\AdminNewRequestMail;
use App\Mail\CustomerRequestConfirmationMail;
use Illuminate\Support\Facades\Mail;

class SharersWizardController extends Controller
{
    private array $steps = ['dati', 'documento', 'contatti', 'indirizzi', 'numero', 'servizi', 'pagamento'];

    public function show(string $step)
    {
        abort_unless(in_array($step, $this->steps, true), 404);

        $store = $this->currentStore();

        if ($store) {
            abort_unless($store->is_active, 404);
            session(['sharers_store_id' => $store->id]);
        } elseif ($step === 'dati') {
            session()->forget('sharers_store_id');
        }

        return view("wizard.$step", [
            'step' => $step,
            'steps' => $this->steps,
            'data' => session('sharers_form', []),
            'store' => $store,
        ]);
    }

    public function store(string $step, Request $request)
    {
        abort_unless(in_array($step, $this->steps, true), 404);

        $store = $this->currentStore();

        if ($store) {
            abort_unless($store->is_active, 404);
            session(['sharers_store_id' => $store->id]);
        } elseif ($step === 'dati') {
            session()->forget('sharers_store_id');
        }

        $validated = match ($step) {
            'dati' => $request->validate([
                'nome' => ['required', 'string', 'max:60'],
                'cognome' => ['required', 'string', 'max:60'],
                'codice_fiscale' => ['required', 'string', 'max:16'],
                'consensi.accetta_condizioni' => ['accepted'],
                'consensi.attivazione_immediata' => ['accepted'],
                'consensi.marketing' => ['nullable'],
            ]),

            'documento' => $request->validate([
                'tipo_documento' => ['required', 'in:carta_identita,passaporto,patente'],
                'numero_documento' => ['required', 'string', 'max:30'],
                'data_scadenza' => ['required', 'date'],
            ]),

            'contatti' => $request->validate([
                'email' => ['required', 'email', 'max:120'],
                'email_confirm' => ['required', 'same:email'],
                'cellulare' => ['required', 'string', 'max:20'],
            ]),

            'indirizzi' => $request->validate([
                'spedizione.destinatario' => ['required', 'string', 'max:120'],
                'spedizione.cap' => ['required', 'string', 'max:10'],
                'spedizione.citta' => ['required', 'string', 'max:120'],
                'spedizione.indirizzo' => ['required', 'string', 'max:160'],
                'spedizione.civico' => ['required', 'string', 'max:20'],
                'residenza_diversa' => ['nullable'],
                'residenza.cap' => ['nullable', 'required_if:residenza_diversa,1', 'string', 'max:10'],
                'residenza.citta' => ['nullable', 'required_if:residenza_diversa,1', 'string', 'max:120'],
                'residenza.indirizzo' => ['nullable', 'required_if:residenza_diversa,1', 'string', 'max:160'],
                'residenza.civico' => ['nullable', 'required_if:residenza_diversa,1', 'string', 'max:20'],
            ]),

            'numero' => $request->validate([
                'scelta' => ['required', 'in:nuovo,portabilita'],
            ]),

            'servizi' => $request->validate([
                'opzione_5g' => ['nullable'],
                'ricarica_automatica' => ['nullable'],
                'safe_call' => ['nullable'],
                'total_security' => ['nullable'],
            ]),

            'pagamento' => $request->validate([
                'metodo' => ['required', 'in:carta,negozio,dopo'],
                'codice_amico' => ['nullable', 'string', 'max:50'],
            ]),

            default => $request->all(),
        };

        $data = session('sharers_form', []);
        $data[$step] = $validated;
        session(['sharers_form' => $data]);

        if ($step === 'pagamento') {
            return redirect()->route('wizard.checkout');
        }

        return redirect()->route('wizard.show', ['step' => $this->nextStep($step)]);
    }

    public function checkout()
    {
        $data = session('sharers_form', []);

        if (empty($data)) {
            return redirect()->route('wizard.show', ['step' => 'dati']);
        }

        $totale = $this->calculateTotal($data);

        return view('wizard.checkout', [
            'data' => $data,
            'totale' => $totale,
            'steps' => $this->steps,
            'step' => 'pagamento',
            'store' => $this->currentStore(),
        ]);
    }

    public function pay()
    {
        $data = session('sharers_form', []);

        if (empty($data)) {
            return redirect()->route('wizard.show', ['step' => 'dati']);
        }

        $totale = $this->calculateTotal($data);

        $submission = FormSubmission::create([
            'store_id'        => $this->storeIdForSubmission(),
            'source'          => $this->storeIdForSubmission() ? 'store_link' : 'public',
            'service_type'    => 'sim',
            'customer_name'  => trim(($data['dati']['nome'] ?? '') . ' ' . ($data['dati']['cognome'] ?? '')),
            'customer_email' => $data['contatti']['email'] ?? null,
            'customer_phone' => $data['contatti']['cellulare'] ?? null,
            'status'         => 'nuova',
            'activation_status' => 'richiesta',
            'payment_method' => 'carta',
            'payment_status' => 'paid',
            'total_amount'   => $totale,
            'commission_status' => 'non_maturata',
            'payload'        => $data,
        ]);

        $this->sendSubmissionEmails($submission);

        session(['last_submission_id' => $submission->id]);
        session()->forget('sharers_form');

        return redirect()->route('wizard.success');
    }

    public function completeOffline()
    {
        $data = session('sharers_form', []);

        if (empty($data)) {
            return redirect()->route('wizard.show', ['step' => 'dati']);
        }

        $totale = $this->calculateTotal($data);
        $metodo = $data['pagamento']['metodo'] ?? 'dopo';

        $submission = FormSubmission::create([
            'store_id'        => $this->storeIdForSubmission(),
            'source'          => $this->storeIdForSubmission() ? 'store_link' : 'public',
            'service_type'    => 'sim',
            'customer_name'  => trim(($data['dati']['nome'] ?? '') . ' ' . ($data['dati']['cognome'] ?? '')),
            'customer_email' => $data['contatti']['email'] ?? null,
            'customer_phone' => $data['contatti']['cellulare'] ?? null,
            'status'         => 'nuova',
            'activation_status' => 'richiesta',
            'payment_method' => $metodo,
            'payment_status' => 'pending',
            'total_amount'   => $totale,
            'commission_status' => 'non_maturata',
            'payload'        => $data,
        ]);

        $this->sendSubmissionEmails($submission);

        session(['last_submission_id' => $submission->id]);
        session()->forget('sharers_form');

        return redirect()->route('wizard.offlineSuccess');
    }

    public function success()
    {
        return view('wizard.success', [
            'steps' => $this->steps,
            'step' => 'pagamento',
            'message' => 'Operazione completata correttamente.',
            'store' => $this->currentStore(),
        ]);
    }

    public function offlineSuccess()
    {
        return view('wizard.success', [
            'steps' => $this->steps,
            'step' => 'pagamento',
            'message' => 'Richiesta inviata correttamente. Il pagamento sarà gestito secondo la modalità scelta.',
            'store' => $this->currentStore(),
        ]);
    }

    public function cancel()
    {
        return redirect()->route('wizard.checkout')
            ->with('error', 'Pagamento annullato.');
    }

    public function reset()
    {
        session()->forget('sharers_form');
        session()->forget('last_submission_id');
        session()->forget('sharers_store_id');

        return redirect()->route('wizard.show', ['step' => 'dati']);
    }

    private function calculateTotal(array $data): float
    {
        $totale = 14.85;

        if (!empty($data['servizi']['opzione_5g'])) {
            $totale += 1.95;
        }

        if (!empty($data['servizi']['safe_call'])) {
            $totale += 0.95;
        }

        if (!empty($data['servizi']['total_security'])) {
            $totale += 1.95;
        }

        return $totale;
    }

    private function sendSubmissionEmails(FormSubmission $submission): void
    {
        $adminEmail = config('mail.admin_to', env('FORM_ADMIN_EMAIL'));

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new AdminNewRequestMail($submission));
        }

        if ($submission->customer_email) {
            Mail::to($submission->customer_email)->send(new CustomerRequestConfirmationMail($submission));
        }
    }

    private function nextStep(string $current): string
    {
        $i = array_search($current, $this->steps, true);

        return $this->steps[min($i + 1, count($this->steps) - 1)];
    }

    private function currentStore(): ?Store
    {
        // 1. Slug nell'URL: /negozi/{slug}/attivazione/...
        $slug = request()->route('slug');
        if ($slug) {
            $store = Store::where('slug', $slug)->first();
            if ($store) {
                return $store;
            }
            // Slug presente ma negozio non trovato → 404
            abort(404);
        }

        // 2. Custom domain risolto dal middleware ResolveCustomDomain
        $fromDomain = request()->attributes->get('resolved_store');
        if ($fromDomain instanceof Store) {
            return $fromDomain;
        }

        return null;
    }

    private function storeIdForSubmission(): ?int
    {
        return $this->currentStore()?->id ?? session('sharers_store_id');
    }
}
