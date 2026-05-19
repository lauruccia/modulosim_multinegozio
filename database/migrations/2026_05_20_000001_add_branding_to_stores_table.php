<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('is_active');
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('primary_color', 7)->default('#dddc00')->after('favicon_path');
            $table->string('secondary_color', 7)->default('#1d1d1b')->after('primary_color');
            $table->string('font_family')->default('Arial')->after('secondary_color');
            $table->string('custom_name')->nullable()->after('font_family');
            $table->string('custom_domain')->nullable()->unique()->after('custom_name');
            $table->json('custom_texts')->nullable()->after('custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path', 'favicon_path',
                'primary_color', 'secondary_color',
                'font_family', 'custom_name', 'custom_domain', 'custom_texts',
            ]);
        });
    }
};
