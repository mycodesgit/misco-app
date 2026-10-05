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
        Schema::create('dailyticketrequest', function (Blueprint $table) {
            $table->id();
            // Requester Details
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reqoff_id')->constrained('offices')->onDelete('cascade');
            $table->foreignId('off_id')->constrained('offices')->onDelete('cascade');

            // Classification (Relates to same Category/Subcategory system)
            $table->foreignId('cat_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcat_id')->constrained('subcategories')->onDelete('cascade');

            // Request Details
            $table->string('ticket_number')->unique(); // e.g., TKT-20261002-0001
            $table->text('issue_description');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->string('contactno')->nullable(); // Optional contact number for follow-up
            $table->string('attachment')->nullable(); // Optional file attachment (e.g., screenshot, document)
            $table->string('remarks')->nullable(); // Optional remarks or additional notes

            // IT Assignment & Tracking
            $table->string('assigned_to')->nullable(); // IT staff assigned
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Cancelled'])->default('Pending');

            // Timestamps for SLA & Accomplishment Reporting
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailyticketrequest');
    }
};
