<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->unique()->after('role')->constrained('customers')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->unique()->after('customer_id')->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['customer_id', 'teacher_id']);
        });
    }
};
