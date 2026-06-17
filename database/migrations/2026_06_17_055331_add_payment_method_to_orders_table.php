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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('full_name')->after('user_id');
            $table->string('email')->nullable()->after('full_name');
            $table->string('phone')->after('email');
            $table->text('address')->after('phone');

            // Payment Method Column
            $table->enum('payment_method', ['cash_on_delivery', 'card'])->default('card')->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'email',
                'phone',
                'address',
                'payment_method'
            ]);
        });
    }
};
