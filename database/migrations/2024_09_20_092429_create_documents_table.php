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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('amount_paid')->default(0);
            $table->timestamp('due_date', 6)->useCurrent();
            $table->morphs('documentable');
            $table->timestamp('payment_date', 6)->nullable()->default(null);
            $table->enum('payment_status', [
                'overdue',
                'paid',
                'pending',
                'refunded',
            ]);
            $table->enum('type', ['invoice', 'receipt']);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('uuid')->unique();
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
