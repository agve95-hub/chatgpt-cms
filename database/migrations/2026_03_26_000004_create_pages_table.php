<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('url_path');
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->json('lighthouse_score')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->string('content_hash', 64)->nullable()->index();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'file_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
