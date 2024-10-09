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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->float('discount')->default(0.00);
            $table->text('notes')->nullable();
            $table->enum('payment_method', [
                'card',
                'cash',
                'wire transfer',
            ])->default('cash');
            $table->enum('payment_status', [
                'overdue',
                'paid',
                'pending',
                'refunded',
            ])->default('paid');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('shipping')->default(0);
            $table->unsignedInteger('tendered')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('uuid')->unique();
            $table->float('vat')->default(0.00);
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
