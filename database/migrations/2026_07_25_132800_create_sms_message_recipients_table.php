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
        Schema::create('sms_message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('sms_messages')->cascadeOnDelete();
            $table->string('mobile');
            $table->string('status')->default('pending');
            $table->string('reference_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('mobile');
            $table->index('status');
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_message_recipients');
    }
};
