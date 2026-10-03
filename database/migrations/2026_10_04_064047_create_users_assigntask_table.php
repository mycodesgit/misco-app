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
        Schema::create('users_assigntask', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('taskassigned')->nullable();
            $table->string('aboutassigned')->nullable();
            $table->timestamps();
        });
        
        $admin = DB::table('users')->where('email', 'superadmin@cpsu.edu.ph')->first();

        if ($admin) {
            // 3. Build payload matching users_assigntask schema
            $assignTaskData = [
                'user_id'       => $admin->id,
                'taskassigned'  => null,
                'aboutassigned' => null,
            ];

            // 3. Insert audit log
            DB::table('users_assigntask')->insert([
                'user_id' => $admin->id,
                'taskassigned' => null,
                'aboutassigned' => null,
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
        Schema::dropIfExists('users_assigntask');
    }
};
