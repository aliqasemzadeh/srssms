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
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->string('source')->default('panel')->after('user_id');
            $table->foreignId('token_id')->nullable()->after('source')->constrained('sms_tokens')->nullOnDelete();

            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('token_id');
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
