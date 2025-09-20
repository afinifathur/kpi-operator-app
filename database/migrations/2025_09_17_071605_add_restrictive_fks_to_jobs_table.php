<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catatan:
        // - Kita cek eksistensi FK berbasis information_schema.KEY_COLUMN_USAGE
        //   agar idempotent, terlepas dari nama constraint yang sudah ada.
        // - ON DELETE RESTRICT menjaga agar master tidak bisa dihapus kalau ada jobs.
        // - ON UPDATE CASCADE biar aman saat id master berubah (jarang terjadi, tapi aman).

        $this->addFkIfMissing('jobs', 'item_id',     'items',     'id', 'fk_jobs_item_id_items');
        $this->addFkIfMissing('jobs', 'machine_id',  'machines',  'id', 'fk_jobs_machine_id_machines');
        $this->addFkIfMissing('jobs', 'operator_id', 'operators', 'id', 'fk_jobs_operator_id_operators');
        $this->addFkIfMissing('jobs', 'shift_id',    'shifts',    'id', 'fk_jobs_shift_id_shifts');
    }

    public function down(): void
    {
        // Hapus FK bila ada (tanpa error kalau tidak ada)
        $this->dropFkIfExists('jobs', 'fk_jobs_item_id_items');
        $this->dropFkIfExists('jobs', 'fk_jobs_machine_id_machines');
        $this->dropFkIfExists('jobs', 'fk_jobs_operator_id_operators');
        $this->dropFkIfExists('jobs', 'fk_jobs_shift_id_shifts');
    }

    private function addFkIfMissing(string $table, string $column, string $refTable, string $refColumn, string $constraintName): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable($refTable)) {
            return;
        }

        // Lewati jika kolom sumber tidak ada
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        // 1) Cek apakah kolom sudah punya FK (apapun nama constraint-nya)
        if ($this->foreignKeyOnColumnExists($table, $column)) {
            return;
        }

        // 2) Jika belum ada, cek apakah nama constraint yang akan kita pakai sudah terpakai
        if ($this->constraintNameExists($table, $constraintName)) {
            // Nama sudah ada (mungkin dari migrasi sebelumnya); jangan tambahkan lagi.
            return;
        }

        // 3) Tambahkan FK RESTRICT idempotent
        $sql = sprintf(
            'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE RESTRICT ON UPDATE CASCADE',
            $table,
            $constraintName,
            $column,
            $refTable,
            $refColumn
        );

        DB::statement($sql);
    }

    private function dropFkIfExists(string $table, string $constraintName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->constraintNameExists($table, $constraintName)) {
            return;
        }

        $sql = sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraintName);
        DB::statement($sql);
    }

    private function foreignKeyOnColumnExists(string $table, string $column): bool
    {
        $db = DB::getDatabaseName();

        $exists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        return $exists;
    }

    private function constraintNameExists(string $table, string $constraintName): bool
    {
        $db = DB::getDatabaseName();

        // Cek di TABLE_CONSTRAINTS (FK) untuk nama constraint tertentu
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        return $exists;
    }
};
