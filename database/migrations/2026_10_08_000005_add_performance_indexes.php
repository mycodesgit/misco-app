<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NOTE: plain foreign keys are NOT listed here on purpose — InnoDB
     * automatically creates an index on every constrained FK column
     * (users.office_id, dailyticketrequest.user_id, ticketchats.ticket_id,
     * clientfeedback.ticket_id, project_members.project_id, ... all covered).
     *
     * Below are only the genuinely-missing indexes, matched 1:1 to the
     * app's real query patterns (status scopes, month/year displays,
     * reverse membership lookups) so big-data searches stay fast.
     */
    public function up(): void
    {
        // --- Audit trails: every display is month/year + newest-first ---
        foreach ([
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
        ] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        // --- Tickets: status-scoped lists per office / per requester ---
        Schema::table('dailyticketrequest', function (Blueprint $table) {
            $table->index(['status', 'off_id']);
            $table->index(['status', 'user_id']);
            $table->index('created_at');
        });

        // --- Feedback report: month/year display + rating summary ---
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->index('created_at');
        });

        // --- Chats are read per ticket in time order ---
        // (already covered by the ticketchats [ticket_id, created_at] index)

        // --- Project members: reverse lookup (which projects is user X in?) ---
        // Forward direction is covered by the unique [project_id, user_id].
        Schema::table('project_members', function (Blueprint $table) {
            $table->index('user_id');
        });

        // --- Daily tasks: year/month display ---
        Schema::table('dailytask', function (Blueprint $table) {
            $table->index('created_at');
        });

        // --- Category pickers: status + office / status filters ---
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['status', 'off_id']);
        });
        Schema::table('subcategories', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        foreach ([
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
        ] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }

        Schema::table('dailyticketrequest', function (Blueprint $table) {
            $table->dropIndex(['status', 'off_id']);
            $table->dropIndex(['status', 'user_id']);
            $table->dropIndex(['created_at']);
        });
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('dailytask', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['status', 'off_id']);
        });
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
