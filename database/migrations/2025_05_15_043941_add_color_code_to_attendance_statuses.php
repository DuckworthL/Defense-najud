<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('attendance_statuses', 'color_code')) {
            Schema::table('attendance_statuses', function (Blueprint $table) {
                $table->string('color_code')->nullable()->after('name');
            });
        }
        
        // Update existing statuses with default colors
        // This part can still run regardless of whether the column was just added or already existed
        DB::table('attendance_statuses')->where('name', 'Present')->update(['color_code' => '#28a745']);
        DB::table('attendance_statuses')->where('name', 'Late')->update(['color_code' => '#ffc107']);
        DB::table('attendance_statuses')->where('name', 'Absent')->update(['color_code' => '#dc3545']);
        DB::table('attendance_statuses')->where('name', 'On Leave')->update(['color_code' => '#6c757d']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attendance_statuses', 'color_code')) {
            Schema::table('attendance_statuses', function (Blueprint $table) {
                $table->dropColumn('color_code');
            });
        }
    }
};