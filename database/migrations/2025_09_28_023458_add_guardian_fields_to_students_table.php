<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students','guardian_name'))      $table->string('guardian_name',120)->nullable();
            if (!Schema::hasColumn('students','guardian_relation'))  $table->string('guardian_relation',120)->nullable();
            if (!Schema::hasColumn('students','guardian_phone'))     $table->string('guardian_phone',120)->nullable();
            if (!Schema::hasColumn('students','guardian_email'))     $table->string('guardian_email',50)->nullable();
            if (!Schema::hasColumn('students','guardian_address'))   $table->string('guardian_address',190)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students','guardian_name'))      $table->dropColumn('guardian_name');
            if (Schema::hasColumn('students','guardian_relation'))  $table->dropColumn('guardian_relation');
            if (Schema::hasColumn('students','guardian_phone'))     $table->dropColumn('guardian_phone');
            if (Schema::hasColumn('students','guardian_email'))     $table->dropColumn('guardian_email');
            if (Schema::hasColumn('students','guardian_address'))   $table->dropColumn('guardian_address');
        });
    }
};
