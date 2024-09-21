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
            $table->enum('category', [
                'pain-relievers',
                'cold-and-flu-remedies',
                'allergy-medications',
                'antacids',
                'vitamins-and-supplements',
                'first-aid-supplies',
                'antibiotics',
                'antidepressants',
                'antihypertensives',
                'cardiovascular-medications',
                'diabetes-medications',
                'respiratory-medications',
                'oncology-medications',
                'blood-pressure-monitors',
                'glucose-meters',
                'thermometers',
                'nebulizers',
                'hearing-aids',
                'contact-lenses and solutions',
                'skincare-products',
                'haircare-products',
                'cosmetics',
                'oral-hygiene-products',
                'baby-products',
                'homeopathic-remedies',
                'herbal-supplements',
                'veterinary-products',
                'medical-equipment',

            ]);
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
