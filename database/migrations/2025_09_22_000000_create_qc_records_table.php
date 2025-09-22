<?php
// bagian ini yang ditambah

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qc_records', function (Blueprint $table) {
            $table->id();
            $table->string('customer', 120)->index();
            $table->string('heat_number', 120)->index();
            $table->string('item', 160)->index();
            $table->string('hasil', 32)->index(); // OK / NG / dll
            $table->string('operator', 120)->index();
            $table->string('department', 120)->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_records');
    }
};
