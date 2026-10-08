<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dailyticketrequest', function (Blueprint $table) {
            $table->unsignedBigInteger('resolved_by')->nullable()->after('assigned_to');
            $table->foreign('resolved_by', 'dailyticketrequest_resolved_by_foreign')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dailyticketrequest', function (Blueprint $table) {
            $table->dropForeign('dailyticketrequest_resolved_by_foreign');
            $table->dropColumn('resolved_by');
        });
    }
};
