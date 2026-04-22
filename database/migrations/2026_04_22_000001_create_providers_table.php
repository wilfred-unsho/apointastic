<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name', 150);
            $table->string('timezone', 64)->default('UTC');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['approval_status', 'timezone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
