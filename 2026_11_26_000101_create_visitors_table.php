<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('email', 150);
            $table->string('phone', 15);
            $table->string('company', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('id_proof_type', 30);
            $table->string('id_proof_number', 255); // stored encrypted
            $table->string('photo', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->string('blacklist_reason', 255)->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
