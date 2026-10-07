<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->unsignedBigInteger('off_id')->nullable()->after('subcat_id');
            $table->foreign('off_id', 'clientfeedback_off_id_foreign')
                ->references('id')->on('offices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientfeedback', function (Blueprint $table) {
            $table->dropForeign('clientfeedback_off_id_foreign');
            $table->dropColumn('off_id');
        });
    }
};
