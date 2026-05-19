<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('tracking_token', 64)->unique()->nullable()->after('id');
            $table->timestamp('tracking_notified_at')->nullable()->after('commission_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'tracking_notified_at']);
        });
    }
};
