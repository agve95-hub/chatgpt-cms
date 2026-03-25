<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('repo_url')->unique();
            $table->string('repo_provider')->default('github');
            $table->string('repo_branch')->default('main');
            $table->string('local_repo_path')->nullable();
            $table->string('project_type')->nullable();
            $table->string('build_command')->nullable();
            $table->string('build_output_dir')->nullable();
            $table->string('deploy_path')->nullable();
            $table->string('status')->default('pending');
            $table->string('github_webhook_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
