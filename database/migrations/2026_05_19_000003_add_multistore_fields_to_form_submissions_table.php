<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
            $table->string('source')->default('public')->after('submitted_by_user_id');
            $table->string('activation_status')->default('richiesta')->after('status');
            $table->timestamp('activated_at')->nullable()->after('activation_status');
            $table->decimal('commission_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('commission_status')->default('non_maturata')->after('commission_amount');
            $table->timestamp('commission_confirmed_at')->nullable()->after('commission_status');
            $table->timestamp('commission_paid_at')->nullable()->after('commission_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropConstrainedForeignId('submitted_by_user_id');
            $table->dropColumn([
                'source',
                'activation_status',
                'activated_at',
                'commission_amount',
                'commission_status',
                'commission_confirmed_at',
                'commission_paid_at',
            ]);
        });
    }
};
