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
        Schema::table('products', function (Blueprint $table) {
            $table->string('lulu_book_id')->nullable();
            $table->string('book_size')->nullable();
            $table->string('page_count')->nullable();
            $table->string('binding_type')->nullable();
            $table->string('interior_color')->nullable();
            $table->string('paper_type')->nullable();
            $table->string('cover_finish')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('interior_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
