<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type')->default('sim')->index();
            $table->string('trigger_status')->default('attivata')->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('service_type')->default('sim')->after('source');
            $table->foreignId('commission_rule_id')
                ->nullable()
                ->after('commission_amount')
                ->constrained('commission_rules')
                ->nullOnDelete();
        });

        DB::table('commission_rules')->insert([
            'name' => 'Attivazione SIM conclusa',
            'service_type' => 'sim',
            'trigger_status' => 'attivata',
            'amount' => 5.00,
            'is_active' => true,
            'notes' => 'Regola iniziale: commissione fissa per SIM attivata.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('commission_rule_id');
            $table->dropColumn('service_type');
        });

        Schema::dropIfExists('commission_rules');
    }
};
