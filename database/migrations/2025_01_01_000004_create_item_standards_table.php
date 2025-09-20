<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->integer('std_time_sec_per_pcs');
            $table->date('aktif_dari');
            $table->date('aktif_sampai')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('item_standards'); }
};
