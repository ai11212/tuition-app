<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexNames(): array
    {
        return collect(Schema::getIndexes('student_attendances'))
            ->pluck('name')->map(fn($n) => strtolower((string) $n))->all();
    }

    /**
     * A duplicate attendance is the same student in the same time slot on a
     * date — not the same subject. Swap the unique index from
     * (student_id, date, subject) to (student_id, date, time).
     *
     * The old index also backs the student_id foreign key, so a replacement
     * index covering student_id (leftmost) must be added BEFORE it is dropped.
     * The swap never deletes or edits existing rows; if live data already has a
     * genuine (student_id, date, time) collision the unique index is skipped
     * (a plain student_id index is added to keep the FK valid) and the PHP
     * guard remains the enforcement.
     */
    public function up(): void
    {
        // Exclude null/empty time — unique indexes treat NULL as distinct.
        $hasConflict = DB::table('student_attendances')
            ->whereNotNull('time')->where('time', '<>', '')
            ->select('student_id', 'date', 'time')
            ->groupBy('student_id', 'date', 'time')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        // 1. Add a replacement index that covers student_id, before the drop.
        $indexes = $this->indexNames();
        if (!$hasConflict) {
            if (!in_array('student_attendances_student_id_date_time_unique', $indexes, true)) {
                Schema::table('student_attendances', function (Blueprint $t) {
                    $t->unique(['student_id', 'date', 'time']);
                });
            }
        } else {
            if (!in_array('student_attendances_student_id_index', $indexes, true)) {
                Schema::table('student_attendances', function (Blueprint $t) {
                    $t->index('student_id');
                });
            }
        }

        // 2. Drop the old subject-based unique index (now that the FK has cover).
        $indexes = $this->indexNames();
        if (in_array('student_attendances_student_id_date_subject_unique', $indexes, true)) {
            Schema::table('student_attendances', function (Blueprint $t) {
                $t->dropUnique(['student_id', 'date', 'subject']);
            });
        }
    }

    public function down(): void
    {
        $hasConflict = DB::table('student_attendances')
            ->whereNotNull('subject')->where('subject', '<>', '')
            ->select('student_id', 'date', 'subject')
            ->groupBy('student_id', 'date', 'subject')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        // Re-add a student_id-covering index before dropping the time unique one.
        $indexes = $this->indexNames();
        if (!$hasConflict) {
            if (!in_array('student_attendances_student_id_date_subject_unique', $indexes, true)) {
                Schema::table('student_attendances', function (Blueprint $t) {
                    $t->unique(['student_id', 'date', 'subject']);
                });
            }
        } else {
            if (!in_array('student_attendances_student_id_index', $indexes, true)) {
                Schema::table('student_attendances', function (Blueprint $t) {
                    $t->index('student_id');
                });
            }
        }

        $indexes = $this->indexNames();
        if (in_array('student_attendances_student_id_date_time_unique', $indexes, true)) {
            Schema::table('student_attendances', function (Blueprint $t) {
                $t->dropUnique(['student_id', 'date', 'time']);
            });
        }
    }
};
