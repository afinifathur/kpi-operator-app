<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
      if (! Schema::hasTable('qc_operators')) {
            Schema::create('qc_operators', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120)->index();       // nama operator QC
                $table->string('department', 120)->index(); // departemen: Netto/Bubut/Cor/dll
               $table->boolean('active')->default(true);
                $table->timestamps();
            });  
    }
  }

    public function down(): void
    {
        if (Schema::hasTable('qc_operators')) {
            Schema::drop('qc_operators');
    }
  }
};
