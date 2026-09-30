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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete(); // Or ->onDelete('cascade') depending on requirements
            $table->string('campus_id')->nullable();
            $table->enum('ustatus', [1, 2, 3])->default(1);
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('gender')->nullable();
            $table->string('posted_by')->nullable();
            $table->string('isAllowed')->default('No');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'office_id' => null,
            'campus_id' => null,
            'ustatus' => 1,
            'fname' => 'Super',
            'mname' => '',
            'lname' => 'Admin',
            'email' => 'superadmin@cpsu.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'Administrator',
            'gender' => 'Male',
            'posted_by' => 'System',
            'isAllowed' => 'No',
            'remember_token' => Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
