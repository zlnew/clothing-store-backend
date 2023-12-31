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
            $table->unsignedInteger('stock');
            $table->string('category', 5);
            $table->string('name')->unique();
            $table->unsignedDecimal('price', 15, 2);
            $table->json('sizes');
            $table->text('image');
            $table->text('description');
            $table->text('slug');
            $table->unsignedDecimal('discount_percentage', 5, 2)->default(0);
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
