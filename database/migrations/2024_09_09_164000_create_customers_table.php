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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->string('email')->unique();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->foreignId('loyalty_program_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('loyalty_program_member')->default(false);
            $table->string('name');
            $table->string('phone');
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
        Schema::dropIfExists('customers');
    }
};
