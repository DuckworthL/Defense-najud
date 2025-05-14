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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('attendance_status_id')->constrained('attendance_statuses');
            $table->date('date');
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            $table->boolean('is_clock_in_reset')->default(false);
            $table->unsignedBigInteger('clock_in_reset_by')->nullable();
            $table->text('clock_in_reset_reason')->nullable();
            $table->boolean('is_clock_out_reset')->default(false);
            $table->unsignedBigInteger('clock_out_reset_by')->nullable();
            $table->text('clock_out_reset_reason')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verification_time')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Add indexes
            $table->index('date');
            $table->index(['employee_id', 'date']);

            // Add foreign key constraints for the reset_by and verified_by fields
            $table->foreign('clock_in_reset_by')->references('id')->on('employees');
            $table->foreign('clock_out_reset_by')->references('id')->on('employees');
            $table->foreign('verified_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};