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
        Schema::create('users_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('off_id')->constrained('offices')->onDelete('cascade');
            $table->string('rolename');
            $table->enum('status', [1, 2, 3])->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_role');
    }
};
