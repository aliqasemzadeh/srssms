<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->unsignedBigInteger('sms_rate')->default(1500)->after('is_public');
        });

        Schema::table('sms_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('sms_rate')->nullable()->after('parts_count');
            $table->unsignedBigInteger('cost')->nullable()->after('sms_rate');
        });

        Schema::table('sms_message_recipients', function (Blueprint $table) {
            $table->foreignId('contact_id')
                ->nullable()
                ->after('message_id')
                ->constrained('phonebook_contacts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sms_message_recipients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
        });

        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropColumn(['sms_rate', 'cost']);
        });

        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->dropColumn('sms_rate');
        });
    }
};
