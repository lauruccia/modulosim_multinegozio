<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();

            // tipo: ricevuta | in_lavorazione | attivata | respinta | annullata | nota
            $table->string('event_type')->default('nota');
            $table->string('title');
            $table->text('description')->nullable();

            // se true il cliente lo vede nella pagina di tracking
            $table->boolean('is_visible_to_customer')->default(true);

            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['form_submission_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_events');
    }
};
