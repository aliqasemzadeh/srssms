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
        Schema::create('sms_token_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('token_id')->nullable()->constrained('sms_tokens')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('method', 10);
            $table->string('path');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->foreignId('message_id')->nullable()->constrained('sms_messages')->nullOnDelete();
            $table->timestamps();

            $table->index('token_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_token_logs');
    }
};
