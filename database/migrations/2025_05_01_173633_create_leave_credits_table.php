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
        Schema::create('leave_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->year('fiscal_year');
            $table->decimal('allocated_days', 5, 2);
            $table->decimal('used_days', 5, 2)->default(0);
            
            // For MySQL 8.0+ and MariaDB 10.2+ support for generated columns
            if (env('DB_CONNECTION') === 'mysql') {
                $table->decimal('remaining_days', 5, 2)
                    ->virtualAs('allocated_days - used_days');
            } else {
                $table->decimal('remaining_days', 5, 2)
                    ->storedAs('allocated_days - used_days');
            }
            
            $table->date('expiry_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees');
            $table->foreignId('updated_by')->nullable()->constrained('employees');
            $table->timestamps();
            
            // Add indexes
            $table->index(['employee_id', 'leave_type_id', 'fiscal_year']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credits');
    }
};