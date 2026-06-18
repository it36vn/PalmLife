<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_server_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('platform');
            $table->string('event_id')->nullable();
            $table->string('event_type')->nullable();
            $table->string('product_id')->nullable();
            $table->text('purchase_token')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('received');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['platform', 'event_id']);
            $table->index(['platform', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_server_notifications');
    }
};
