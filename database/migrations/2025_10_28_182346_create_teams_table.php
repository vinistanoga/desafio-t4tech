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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique()->nullable();
            $table->string('conference', 10)->nullable();
            $table->string('division', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('name', 100);
            $table->string('full_name', 150);
            $table->string('abbreviation', 3)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
