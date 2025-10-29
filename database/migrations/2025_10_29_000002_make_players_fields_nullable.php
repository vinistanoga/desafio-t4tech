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
        Schema::table('players', function (Blueprint $table) {
            $table->string('position', 10)->nullable()->change();
            $table->string('height', 10)->nullable()->change();
            $table->string('weight', 10)->nullable()->change();
            $table->string('jersey_number', 10)->nullable()->change();
            $table->string('college', 100)->nullable()->change();
            $table->string('country', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('position', 10)->nullable(false)->change();
            $table->string('height', 10)->nullable(false)->change();
            $table->string('weight', 10)->nullable(false)->change();
            $table->string('jersey_number', 10)->nullable(false)->change();
            $table->string('college', 100)->nullable(false)->change();
            $table->string('country', 100)->nullable(false)->change();
        });
    }
};
