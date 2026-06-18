<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_vi');
            $table->string('name_en');
            $table->unsignedInteger('price_vnd');
            $table->unsignedInteger('quota_limit')->nullable();
            $table->string('quota_period');
            $table->boolean('is_default')->default(false);
            $table->text('description_vi');
            $table->text('description_en');
            $table->string('apple_product_id')->nullable()->unique();
            $table->string('google_product_id')->nullable()->unique();
            $table->string('store_product_type')->default('subscription');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
