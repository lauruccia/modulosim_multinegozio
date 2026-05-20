<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_events', function (Blueprint $table): void {
            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->after('form_submission_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submission_events', function (Blueprint $table): void {
            $table->dropForeign(['performed_by_user_id']);
            $table->dropColumn('performed_by_user_id');
        });
    }
};
