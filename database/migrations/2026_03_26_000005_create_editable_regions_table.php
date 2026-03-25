<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('editable_regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('selector')->nullable();
            $table->string('marker_id')->nullable();
            $table->string('region_type')->default('text');
            $table->boolean('is_static')->default(false);
            $table->string('detection_method')->default('auto');
            $table->decimal('confidence_score', 4, 2)->nullable();
            $table->longText('current_content')->nullable();
            $table->json('source_location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editable_regions');
    }
};
