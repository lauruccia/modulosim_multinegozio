<?php

namespace Tests\Feature;

use App\Filament\Resources\FormSubmissionResource\Pages\CreateFormSubmission;
use App\Models\FormSubmission;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class StoreAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_slug_wizard_assigns_the_submission_to_the_store(): void
    {
        Mail::fake();

        $store = Store::create([
            'name' => 'Negozio Milano',
            'slug' => 'milano',
            'is_active' => true,
        ]);

        $this->completeWizard("negozi/{$store->slug}/attivazione");

        $submission = FormSubmission::first();

        $this->assertNotNull($submission);
        $this->assertSame($store->id, $submission->store_id);
        $this->assertSame('store_link', $submission->source);
        $this->assertSame('sim', $submission->service_type);
    }

    public function test_custom_domain_wizard_assigns_the_submission_to_the_store(): void
    {
        Mail::fake();

        $store = Store::create([
            'name' => 'Negozio Roma',
            'slug' => 'roma',
            'custom_domain' => 'attiva.negozioroma.test',
            'is_active' => true,
        ]);

        $this->completeWizard('attivazione', 'attiva.negozioroma.test', $store->id);

        $submission = FormSubmission::first();

        $this->assertNotNull($submission);
        $this->assertSame($store->id, $submission->store_id);
        $this->assertSame('store_link', $submission->source);
    }

    public function test_public_wizard_leaves_submission_without_store(): void
    {
        Mail::fake();

        $this->completeWizard('attivazione');

        $submission = FormSubmission::first();

        $this->assertNotNull($submission);
        $this->assertNull($submission->store_id);
        $this->assertSame('public', $submission->source);
    }

    public function test_store_user_backend_creation_assigns_own_store(): void
    {
        $store = Store::create([
            'name' => 'Negozio Torino',
            'slug' => 'torino',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'store',
            'store_id' => $store->id,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateFormSubmission::class)
            ->fillForm([
                'service_type' => 'luce',
                'payment_method' => 'negozio',
                'customer_name' => 'Mario Rossi',
                'customer_email' => 'mario@example.test',
                'customer_phone' => '3331234567',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $submission = FormSubmission::first();

        $this->assertNotNull($submission);
        $this->assertSame($store->id, $submission->store_id);
        $this->assertSame($user->id, $submission->submitted_by_user_id);
        $this->assertSame('admin_panel', $submission->source);
        $this->assertSame('luce', $submission->service_type);
    }

    private function completeWizard(string $prefix, string $host = 'localhost', ?int $expectedStoreId = null): void
    {
        $server = [
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
        ];
        $url = fn (string $path): string => $host === 'localhost'
            ? "/{$prefix}{$path}"
            : "http://{$host}/{$prefix}{$path}";

        $response = $this->call('GET', $url('/dati'), [], [], [], $server);
        $response->assertOk();

        if ($expectedStoreId !== null) {
            $response->assertSessionHas('sharers_store_id', $expectedStoreId);
        }

        $this->call('POST', $url('/dati'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'codice_fiscale' => 'RSSMRA80A01H501U',
            'consensi' => [
                'accetta_condizioni' => '1',
                'attivazione_immediata' => '1',
            ],
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/documento'), [
            'tipo_documento' => 'carta_identita',
            'numero_documento' => 'AA1234567',
            'data_scadenza' => now()->addYear()->toDateString(),
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/contatti'), [
            'email' => 'mario@example.test',
            'email_confirm' => 'mario@example.test',
            'cellulare' => '3331234567',
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/indirizzi'), [
            'spedizione' => [
                'destinatario' => 'Mario Rossi',
                'cap' => '00100',
                'citta' => 'Roma',
                'indirizzo' => 'Via Roma',
                'civico' => '1',
            ],
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/numero'), [
            'scelta' => 'nuovo',
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/servizi'), [
            'opzione_5g' => '1',
            'safe_call' => '1',
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/pagamento'), [
            'metodo' => 'negozio',
        ], [], [], $server)->assertRedirect();

        $this->call('POST', $url('/checkout/concludi-offline'), [], [], [], $server)->assertRedirect();
    }
}
