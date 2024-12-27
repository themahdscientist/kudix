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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('description');
            $table->text('dosage')->nullable();
            $table->timestamp('expiry_date', 6)->useCurrent();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->string('sku')->unique();
            $table->enum('status', ['in-stock', 'discontinued', 'out-of-stock'])->default('out-of-stock');
            $table->unsignedInteger('quantity')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
