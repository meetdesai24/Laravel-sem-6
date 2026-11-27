<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users');
            $table->string('purpose', 255);
            $table->date('visit_date');
            $table->time('expected_in');
            $table->time('expected_out')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'checked_in', 'checked_out', 'expired'])
                  ->default('pending');
            $table->char('qr_token', 64)->nullable()->unique();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->index(['visit_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
