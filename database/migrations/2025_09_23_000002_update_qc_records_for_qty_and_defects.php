<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('qc_records', function (Blueprint $table) {
            if (! Schema::hasColumn('qc_records', 'qty')) {
                // ditambah setelah kolom 'item'
                $table->unsignedInteger('qty')->default(0)->after('item');
            }

            if (! Schema::hasColumn('qc_records', 'defects')) {
                // ditambah setelah kolom 'qty'
                $table->unsignedInteger('defects')->default(0)->after('qty');
            }

            if (! Schema::hasColumn('qc_records', 'qc_operator_id')) {
                // FK ke tabel qc_operators, nullable, null on delete
                $table->foreignId('qc_operator_id')
                    ->nullable()
                    ->after('operator')
                    ->constrained('qc_operators')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('qc_records', function (Blueprint $table) {
            if (Schema::hasColumn('qc_records', 'qc_operator_id')) {
                $table->dropConstrainedForeignId('qc_operator_id');
            }

            if (Schema::hasColumn('qc_records', 'qty')) {
                $table->dropColumn('qty');
            }

            if (Schema::hasColumn('qc_records', 'defects')) {
                $table->dropColumn('defects');
            }
        });
    }
};
