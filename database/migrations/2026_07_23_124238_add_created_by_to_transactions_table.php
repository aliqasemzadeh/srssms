<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('wallet_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        $userMorph = (new User)->getMorphClass();

        DB::table('transactions')
            ->where('reference_type', $userMorph)
            ->whereNotNull('reference_id')
            ->whereNull('created_by')
            ->orderBy('id')
            ->each(function (object $transaction): void {
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'created_by' => $transaction->reference_id,
                        'reference_type' => null,
                        'reference_id' => null,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
