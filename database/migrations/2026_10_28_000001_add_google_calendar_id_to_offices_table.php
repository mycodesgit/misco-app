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
        Schema::table('offices', function (Blueprint $table) {
            // The office's shared Google Workspace calendar, e.g.
            // "c_abc123@group.calendar.google.com" or "mis@cpsu.edu.ph"
            $table->string('google_calendar_id')->nullable()->after('office_abbr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('google_calendar_id');
        });
    }
};
