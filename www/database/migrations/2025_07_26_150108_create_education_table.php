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
        Schema::create('education', function (Blueprint $table) {
            $table->id();

            // Bilingual content fields
            $table->string('title_id')->nullable();
            $table->string('title_en')->nullable();
            $table->text('description_id')->nullable();
            $table->text('description_en')->nullable();
            $table->longText('content_id')->nullable();
            $table->longText('content_en')->nullable();

            // Media and categorization
            $table->string('image')->nullable();
            $table->string('category')->default('tutorial');
            $table->json('tags')->nullable();

            // Publishing controls
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // SEO fields - bilingual
            $table->string('meta_title_id')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_id')->nullable();
            $table->text('meta_description_en')->nullable();

            // Additional fields
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);

            // Soft deletes for audit trail
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_published', 'published_at']);
            $table->index('category');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
