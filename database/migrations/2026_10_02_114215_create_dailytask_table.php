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
        Schema::create('dailytask', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('off_id')->constrained('offices')->onDelete('cascade');
            $table->foreignId('cat_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcat_id')->nullable()->constrained('subcategories')->onDelete('cascade');
            $table->string('dailytaskdesc');
            $table->string('type')->default('Daily Task');

            // Timestamps for status transitions
            $table->timestamp('started_at')->nullable();   // Set when status becomes 'In Progress'
            $table->timestamp('completed_at')->nullable(); // Set when status becomes 'Completed'

            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailytask');
    }
};
