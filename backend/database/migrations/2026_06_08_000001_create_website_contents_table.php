<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('label');
            $table->string('type')->default('text');
            $table->longText('value_vi');
            $table->longText('value_en');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_contents');
    }
};
