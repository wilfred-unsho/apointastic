<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('starts_at_utc');
            $table->timestampTz('ends_at_utc');
            $table->string('user_timezone', 64);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['provider_id', 'starts_at_utc']);
            $table->index(['customer_id', 'starts_at_utc']);
            $table->index(['status', 'starts_at_utc']);
            $table->unique(['provider_id', 'starts_at_utc', 'ends_at_utc'], 'provider_slot_unique');
            $table->check('starts_at_utc < ends_at_utc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
