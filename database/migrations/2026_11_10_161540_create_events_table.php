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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('summary', 500);
            $table->text('description')->nullable();
            $table->json('schedule')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->nullable();
            $table->string('venue');
            $table->string('online_url')->nullable();
            $table->unsignedInteger('member_fee')->default(0);
            $table->unsignedInteger('guest_fee')->default(0);
            $table->unsignedTinyInteger('max_guests')->default(0);
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
