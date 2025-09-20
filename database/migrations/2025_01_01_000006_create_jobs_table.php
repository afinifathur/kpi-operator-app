<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('operator_id')->constrained('operators');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('machine_id')->constrained('machines');
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->dateTime('jam_mulai');
            $table->dateTime('jam_selesai');
            $table->integer('qty_hasil')->default(0);
            $table->integer('timer_sec_per_pcs')->nullable();
            $table->enum('sumber_timer', ['std','manual'])->default('std');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('jobs'); }
};
