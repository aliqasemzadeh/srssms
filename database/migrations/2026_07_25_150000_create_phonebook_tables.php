<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phonebook_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('phonebook_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('mobile');
            $table->string('company')->nullable();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('marriage_date')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('national_code', 20)->nullable();
            $table->string('economic_code', 50)->nullable();
            $table->string('person_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'mobile']);
            $table->index(['user_id', 'first_name']);
            $table->index('mobile');
        });

        Schema::create('phonebook_contact_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('phonebook_contacts')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('phonebook_groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_id', 'group_id']);
        });

        Schema::create('phonebook_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('phonebook_contacts')->cascadeOnDelete();
            $table->text('body');
            $table->string('status')->nullable();
            $table->date('remind_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'remind_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phonebook_notes');
        Schema::dropIfExists('phonebook_contact_group');
        Schema::dropIfExists('phonebook_contacts');
        Schema::dropIfExists('phonebook_groups');
    }
};
