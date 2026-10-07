<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->tinyInteger('rating')->nullable()->after('ticket_id')->comment('1=Very Dissatisfied to 5=Very Satisfied');
        });
    }

    public function down(): void
    {
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
