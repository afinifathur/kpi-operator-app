<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('work_minutes')->default(420);
            $table->time('mulai_default')->nullable();
            $table->time('selesai_default')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shifts'); }
};
