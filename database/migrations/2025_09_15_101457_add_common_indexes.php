<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // J O B S
        $this->addIndexIfMissing('jobs', 'operator_id');
        $this->addIndexIfMissing('jobs', 'machine_id');
        $this->addIndexIfMissing('jobs', 'item_id');
        $this->addIndexIfMissing('jobs', 'shift_id');
        $this->addIndexIfMissing('jobs', 'tanggal');
        $this->addIndexIfMissing('jobs', ['tanggal', 'operator_id']);

        // O P E R A T O R S
        $this->addIndexIfMissing('operators', 'departemen');
        $this->addIndexIfMissing('operators', 'no_induk');

        // M A C H I N E S
        $this->addIndexIfMissing('machines', 'departemen');
        $this->addIndexIfMissing('machines', 'no_mesin');

        // J O B   E V A L U A T I O N S
        $this->addUniqueIfMissing('job_evaluations', 'job_id'); // unique

        // I T E M   S T A N D A R D S
        $this->addIndexIfMissing('item_standards', ['item_id', 'aktif_dari']);
        $this->addIndexIfMissing('item_standards', ['item_id', 'aktif_sampai']);
    }

    public function down(): void
    {
        // Optional: drop kalau ada (aman jika tidak ada)
        $this->dropIndexIfExists('jobs', 'jobs_operator_id_index');
        $this->dropIndexIfExists('jobs', 'jobs_machine_id_index');
        $this->dropIndexIfExists('jobs', 'jobs_item_id_index');
        $this->dropIndexIfExists('jobs', 'jobs_shift_id_index');
        $this->dropIndexIfExists('jobs', 'jobs_tanggal_index');
        $this->dropIndexIfExists('jobs', 'jobs_tanggal_operator_id_index');

        $this->dropIndexIfExists('operators', 'operators_departemen_index');
        $this->dropIndexIfExists('operators', 'operators_no_induk_index');

        $this->dropIndexIfExists('machines', 'machines_departemen_index');
        $this->dropIndexIfExists('machines', 'machines_no_mesin_index');

        $this->dropIndexIfExists('job_evaluations', 'job_evaluations_job_id_unique');

        $this->dropIndexIfExists('item_standards', 'item_standards_item_id_aktif_dari_index');
        $this->dropIndexIfExists('item_standards', 'item_standards_item_id_aktif_sampai_index');
    }

    // ==== helpers ==========================================================

    private function addIndexIfMissing(string $table, string|array $columns, ?string $name = null): void
    {
        $name ??= $this->defaultIndexName($table, $columns, 'index');
        if (! $this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                $t->index($columns, $name);
            });
        }
    }

    private function addUniqueIfMissing(string $table, string|array $columns, ?string $name = null): void
    {
        $name ??= $this->defaultIndexName($table, $columns, 'unique');
        if (! $this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                $t->unique($columns, $name);
            });
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($name) {
                $t->dropIndex($name);
            });
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(DB::select("SHOW INDEX FROM `$table` WHERE `Key_name` = ?", [$name]))->isNotEmpty();
    }

    private function defaultIndexName(string $table, string|array $columns, string $suffix): string
    {
        $cols = is_array($columns) ? implode('_', $columns) : $columns;
        return "{$table}_{$cols}_{$suffix}";
    }
};
