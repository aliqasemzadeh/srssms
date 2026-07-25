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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('sms_gateways')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction');
            $table->string('number');
            $table->text('body');
            $table->unsignedSmallInteger('parts_count')->default(1);
            $table->string('encoding')->default('gsm7');
            $table->string('status')->default('pending');
            $table->string('reference_id')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index('direction');
            $table->index('status');
            $table->index('number');
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
