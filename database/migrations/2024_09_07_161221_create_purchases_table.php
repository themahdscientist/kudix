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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->float('discount')->default(0.00);
            $table->timestamp('expected_delivery_date', 6)->useCurrent();
            $table->text('notes')->nullable();
            $table->enum('payment_status', [
                'pending',
                'paid',
                'overdue',
            ])->default('paid');
            $table->enum('order_status', [
                'pending',
                'approved',
                'shipped',
                'received',
            ])->default('received');
            $table->timestamp('received_date', 6)->useCurrent();
            $table->unsignedInteger('shipping')->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
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
        Schema::dropIfExists('purchases');
    }
};
