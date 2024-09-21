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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->string('email')->unique();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->string('phone');
            $table->enum('type', [
                'manufacturer',
                'distributor',
                'wholesaler',
                'importer',
                'drop-shipper',
                'government-agency',
                'non-profit-organization',
                'individual',
            ]);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('website')->nullable();
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
