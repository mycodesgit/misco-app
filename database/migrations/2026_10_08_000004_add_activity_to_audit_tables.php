<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-line, auditor-friendly sentence per row, e.g.
     * "Juan Dela Cruz added new Category 'IT Equipment'".
     * Nullable so historic rows stay valid (the reader falls back to
     * building the sentence from action + payload on the fly).
     */
    private array $tables = [
        'audit_login_logs',
        'audit_trailuser',
        'audit_trailoffice',
        'audit_trailcategory',
        'audit_trailcategorysub',
        'audit_traildailytask',
        'audit_traildailyticketrequest',
        'audit_trailclientfeedback',
        'audit_trailusersrole',
        'audit_trailusers_assigntask',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('activity', 500)->nullable()->after('action');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('activity');
            });
        }
    }
};
