<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('students', 'guardian_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('guardian_name')->nullable()->after('address');
                $table->string('guardian_relation')->nullable()->after('guardian_name');
                $table->string('guardian_phone')->nullable()->after('guardian_relation');
                $table->string('guardian_email')->nullable()->after('guardian_phone');
                $table->string('guardian_address')->nullable()->after('guardian_email');
                $table->string('post_code')->nullable()->after('guardian_address');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('students', 'guardian_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn([
                    'guardian_name','guardian_relation','guardian_phone','guardian_email','guardian_address','post_code'
                ]);
            });
        }
    }
};
