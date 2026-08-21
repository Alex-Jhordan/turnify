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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 20);
            $table->foreignId('category_id')->constrained();
            $table->foreignId('module_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('document_type', 20)->default('dni');
            $table->string('document_number', 30);
            $table->string('name', 255);
            $table->boolean('is_priority')->default(false);
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('call_count')->default(0);
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
