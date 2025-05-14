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
        Schema::table('leave_credits', function (Blueprint $table) {
            // No need to add a remaining_days column as it will be calculated dynamically
            // Just make sure other columns exist
            if (!Schema::hasColumn('leave_credits', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('employees');
            }
            
            if (!Schema::hasColumn('leave_credits', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('employees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_credits', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop columns
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};