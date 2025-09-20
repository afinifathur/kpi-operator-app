<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('no_mesin')->unique();
            $table->string('tipe')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('machines'); }
};
