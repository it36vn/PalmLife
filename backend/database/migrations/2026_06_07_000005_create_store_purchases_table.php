<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('platform');
            $table->string('product_id');
            $table->text('purchase_token');
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['platform', 'transaction_id']);
            $table->index(['user_id', 'platform', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_purchases');
    }
};
