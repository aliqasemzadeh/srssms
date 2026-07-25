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
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->string('access_type')->default('shared')->after('title');
            $table->string('usage_type')->default('service')->after('access_type');
            $table->boolean('is_public')->default(false)->after('usage_type');

            $table->index('access_type');
            $table->index('usage_type');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->dropIndex(['access_type']);
            $table->dropIndex(['usage_type']);
            $table->dropIndex(['is_public']);
            $table->dropColumn(['access_type', 'usage_type', 'is_public']);
        });
    }
};
