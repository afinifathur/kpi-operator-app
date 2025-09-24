<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('qc_operators')) {
            Schema::table('qc_operators', function (Blueprint $table) {
                if (! Schema::hasColumn('qc_operators', 'department')) {
                    $table->string('department', 120)->after('name')->index(); // //bagian ini yang ditambah
                }
                if (! Schema::hasColumn('qc_operators', 'active')) {
                    $table->boolean('active')->default(true)->after('department'); // //bagian ini yang ditambah
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('qc_operators')) {
            Schema::table('qc_operators', function (Blueprint $table) {
                if (Schema::hasColumn('qc_operators', 'active')) {
                    $table->dropColumn('active');
                }
                if (Schema::hasColumn('qc_operators', 'department')) {
                    $table->dropColumn('department');
                }
            });
        }
    }
};
