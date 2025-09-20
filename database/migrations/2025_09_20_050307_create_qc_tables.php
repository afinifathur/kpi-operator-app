<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qc_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('qc_operators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // optional: default department for operator
            $table->foreignId('qc_department_id')->nullable()->constrained('qc_departments')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('customer')->nullable();
            $table->string('heat_number')->index();
            $table->string('item');
            $table->string('result')->nullable(); // mis. OK/NG, Pass/Fail, dll
            $table->foreignId('qc_operator_id')->nullable()->constrained('qc_operators')->nullOnDelete();
            $table->foreignId('qc_department_id')->nullable()->constrained('qc_departments')->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();

            // untuk menghindari duplikasi baris yang sama saat import
            $table->unique(['heat_number', 'item']);
        });

        Schema::create('qc_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_inspection_id')->constrained('qc_inspections')->cascadeOnDelete();
            $table->foreignId('qc_operator_id')->nullable()->constrained('qc_operators')->nullOnDelete();
            $table->foreignId('qc_department_id')->nullable()->constrained('qc_departments')->nullOnDelete();
            $table->unsignedInteger('issue_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['qc_operator_id', 'qc_department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_issues');
        Schema::dropIfExists('qc_inspections');
        Schema::dropIfExists('qc_operators');
        Schema::dropIfExists('qc_departments');
    }
};
