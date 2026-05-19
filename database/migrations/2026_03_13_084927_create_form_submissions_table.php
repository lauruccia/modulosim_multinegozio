<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('form_submissions', function ($table) {
        $table->id();
        $table->string('customer_name')->nullable();
        $table->string('customer_email')->nullable();
        $table->string('customer_phone')->nullable();
        $table->string('status')->default('bozza');
        $table->string('payment_method')->nullable();
        $table->string('payment_status')->default('pending');
        $table->decimal('total_amount', 10, 2)->default(0);
        $table->longText('admin_notes')->nullable();
        $table->json('payload')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
