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
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('phone', 32);
            $table->string('photo_path')->nullable();
            $table->string('degree');
            $table->string('batch', 20)->index();
            $table->string('student_id', 50);
            $table->string('hall')->nullable();
            $table->string('specialization')->index();
            $table->string('position')->nullable();
            $table->string('organization')->nullable();
            $table->string('sector')->index();
            $table->string('location')->nullable();
            $table->boolean('mentor_opt_in')->default(false);
            $table->boolean('show_in_directory')->default(true);
            $table->boolean('newsletter_opt_in')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
