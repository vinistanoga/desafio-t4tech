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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique()->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('position', 10);
            $table->string('height', 10);
            $table->string('weight', 10);
            $table->string('jersey_number', 10);
            $table->string('college', 100);
            $table->string('country', 100);
            $table->integer('draft_year')->nullable();
            $table->integer('draft_round')->nullable();
            $table->integer('draft_number')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
