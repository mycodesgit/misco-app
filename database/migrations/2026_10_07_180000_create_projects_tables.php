<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('Planning');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
