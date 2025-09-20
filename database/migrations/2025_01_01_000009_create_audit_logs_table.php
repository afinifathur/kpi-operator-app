<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi');
            $table->string('tabel');
            $table->string('pk');
            $table->longText('before')->nullable();
            $table->longText('after')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
