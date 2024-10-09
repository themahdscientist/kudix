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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->foreignId('loyalty_program_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['customer', 'patient'])->default(\App\ClientType::Customer->value);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
