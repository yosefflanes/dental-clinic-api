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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->string('transaction_id')->nullable()->comment('ID Asli dari Midtrans');
            $table->string('snap_token')->nullable()->comment('Token untuk pop-up Frontend');
            $table->string('method')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'settlement', 'cancel', 'expire'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
