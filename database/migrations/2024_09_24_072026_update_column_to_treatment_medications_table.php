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
        Schema::table('treatment_medications', function (Blueprint $table) {
            $table->integer('quantity')->nullable();
            $table->string('usage')->nullable();
            $table->string('dosage')->nullable();
            $table->string('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_medications', function (Blueprint $table) {
            //
        });
    }
};
