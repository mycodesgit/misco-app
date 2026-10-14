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
        Schema::create('wifi_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code')->unique();
            $table->string('schlyear');
            $table->string('semester');
            $table->tinyInteger('status')->default(1)->comment('1=Available, 0=Used');
            $table->string('uploaded_by')->nullable();
            $table->timestamps();

            $table->index(['schlyear', 'semester', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_vouchers');
    }
};
