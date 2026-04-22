<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_availabilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('timezone', 64);
            $table->timestampsTz();

            $table->index(['provider_id', 'day_of_week']);
            $table->check('day_of_week between 0 and 6');
            $table->check('start_time < end_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_availabilities');
    }
};
