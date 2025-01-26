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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('category', 255)->nullable();
            $table->string('img_thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration')->nullable();
            $table->date('release_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('trailer_url')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
