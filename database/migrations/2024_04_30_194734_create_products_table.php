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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->double('price', 15, 8)->nullable()->default(0);
            $table->double('disc_price', 15, 8)->nullable()->default(0);
            $table->longText('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('wittenby')->nullable();
            $table->string('publisher')->nullable();
            $table->string('year')->nullable();
            $table->string('pdf')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};