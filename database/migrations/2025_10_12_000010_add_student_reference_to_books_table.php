<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('student_reference')->nullable()->after('reference');
            $table->index('student_reference');
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['student_reference']);
            $table->dropColumn('student_reference');
        });
    }
};