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
        Schema::create('food_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            
            $table->string('shopify_variant_id')->nullable()->index();
            $table->string('title'); // e.g. "Ham", "Large"
            
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            
            $table->string('option1')->nullable(); // e.g. "Protein" or "Ham"
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_variants');
    }
};
