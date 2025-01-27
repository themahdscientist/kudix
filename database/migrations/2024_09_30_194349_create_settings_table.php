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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Account information
            $table->text('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bvn')->nullable();
            $table->string('iso3166_country_code')->nullable();
            $table->enum('kyc', ['pending', 'verified', 'failed'])->default('pending');

            // Company information
            $table->text('company_about');
            $table->string('company_address');
            $table->string('company_logo')->nullable();
            $table->string('company_name');

            // Additional
            $table->float('discount')->default(0.00);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->float('vat')->default(0.00);
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
