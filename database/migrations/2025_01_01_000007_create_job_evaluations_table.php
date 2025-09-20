<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->integer('target_qty');
            $table->decimal('pencapaian_pct',8,2);
            $table->enum('kategori',['LEBIH','ON_TARGET','MENDEKATI','JAUH']);
            $table->enum('auto_flag',['BUTUH_PELATIHAN','PERTAHANKAN','PERTANYAKAN'])->default('PERTAHANKAN');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('job_evaluations'); }
};
