<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('no_induk')->unique();
            $table->string('nama');
            $table->string('departemen')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('operators'); }
};
