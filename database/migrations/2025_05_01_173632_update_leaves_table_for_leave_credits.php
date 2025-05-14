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
        Schema::table('leaves', function (Blueprint $table) {
            // Add leave_type_id column after employee_id
            $table->foreignId('leave_type_id')->after('employee_id')->constrained('leave_types');
            
            // Add is_without_pay column after status
            $table->boolean('is_without_pay')->after('status')->default(false);
            
            // Add user_agent column after remarks
            $table->text('user_agent')->after('remarks')->nullable();
            
            // Add index for leave_type_id
            $table->index('leave_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['leave_type_id']);
            
            // Drop the index
            $table->dropIndex(['leave_type_id']);
            
            // Drop columns
            $table->dropColumn(['leave_type_id', 'is_without_pay', 'user_agent']);
        });
    }
};