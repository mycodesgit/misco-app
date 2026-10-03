<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create audit trail table
        Schema::create('audit_trailusers_assigntask', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('action');
            $table->longText('actiondata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 2. Retrieve administrator user created in users migration
        $admin = DB::table('users')->where('email', 'superadmin@cpsu.edu.ph')->first();

        if ($admin) {
            // 3. Build payload matching users_assigntask schema
            $assignTaskData = [
                'user_id'       => $admin->id,
                'taskassigned'  => null,
                'aboutassigned' => null,
            ];

            // 4. Record audit log entry
            DB::table('audit_trailusers_assigntask')->insert([
                'user_id'    => $admin->id,
                'email'      => $admin->email,
                'action'     => 'Create Super Admin Task Assignment',
                'actiondata' => json_encode($assignTaskData),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Migration',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trailusers_assigntask');
    }
};
