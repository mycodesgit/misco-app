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
        Schema::create('studvoucherreg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vc_id')->constrained('wifi_vouchers')->cascadeOnDelete();
            $table->string('stud_id')->index();
            $table->string('fullname');
            $table->string('role')->default('Student');
            $table->string('schlyear')->index();
            $table->string('semester')->index();
            $table->timestamps();

            $table->index(['schlyear', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studvoucherreg');
    }
};
