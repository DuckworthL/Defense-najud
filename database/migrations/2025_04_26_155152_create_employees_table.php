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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_picture')->nullable();
            $table->date('date_hired');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Add indexes for frequently queried fields
            $table->index('employee_id');
            $table->index('email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};