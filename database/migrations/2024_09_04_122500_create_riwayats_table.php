<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayats', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('arsip_id');
            $table->enum('jenis',['Masuk','Keluar']);
            $table->date('tanggal');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->foreign('arsip_id')->references('id')->on('arsips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluar_masuks');
    }
};
